<?php

namespace App\Controller;
use App\Entity\Actividad;
use App\Entity\Devolucion;
use App\Entity\Pedido;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\SeguidorRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use App\Entity\Direccion;
use App\Entity\Seguidor;
use App\Entity\Sesion;
use App\Form\DireccionType;
use App\Form\ActividadType;
use App\Form\CrearSesionType;
use App\Form\CrearDireccionType;
use App\Form\AsistenciaType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use DateTime;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("/user")
 */
class UsuarioController extends AbstractController
{

    /**
     * @Route("/perfil/{id}", name="perfil")
     */
    public function perfil($id, SeguidorRepository $seguidorRepository, Request $request, /* EntityManagerInterface $em */  SluggerInterface $slugger): Response
    {

        $user = $this->getUser();

        if ($user->getId() != $id) {
            throw $this->createNotFoundException('Usuario no encontrado');
        }

        // Llamar a los métodos de contar del repositorio de Seguidor
        $seguidoresCount = $seguidorRepository->countFollowers($user);
        $seguidosCount = $seguidorRepository->countFollowing($user);

        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('App:Usuario')->find($user->getId());
        $usersSeguidores = $em->getRepository('App:Seguidor')->findBy(['usuario' => $user->getId()]);
        $usersSiguiendo = $em->getRepository('App:Seguidor')->findBy(['follower' => $user->getId()]);

        //Arrays para almacenar sus datos 
        $seguidores = [];
        $siguiendo = [];

        //Busqueda de los seguidores 
        foreach ($usersSeguidores as $seguidor) {
            $usuarioS = $seguidor->getFollower();
            $seguidores[] = [
                'id' => $usuarioS->getId(),
                'nombre' => $usuarioS->getNombre()
            ];
        }

        //Busqueda de a quien sigue 
        foreach ($usersSiguiendo as $sigue) {
            $usuarioSig = $sigue->getUsuario();
            $siguiendo[] = [
                'id' => $usuarioSig->getId(),
                'nombre' => $usuarioSig->getNombre()
            ];
        }

        //Formulario del perfil
        $form_perfil = $this->createForm(\App\Form\PerfilType::class, $usuario);
        $form_perfil->handleRequest($request);

        //Si se cumple se actualiza el nombre del usuario
        if ($form_perfil->isSubmitted() && $form_perfil->isValid()) {
            $nombreFile = $form_perfil->get('nombre')->getData();
            $usuario->setNombre($nombreFile);
            $em->persist($usuario);
            $em->flush();

            return $this->redirectToRoute('perfil', ['id' => $id]);
        }


        return $this->render('usuario/perfil.html.twig', [
            'usuario' => $user,
            'form_perfil' => $form_perfil->createView(),
            'cantidadSeguidores' => $seguidoresCount,
            'cantidadSeguidos' => $seguidosCount,
            'siguiendo' => $siguiendo,
            'seguidores' => $seguidores,
        ]);
    }

    /**
     * @Route("/seguir/{id}", name="seguirUsuario", methods={"POST"})
     */
    public function seguirUsuario($id, Request $request): Response
    {
        $currentUser = $this->getUser();

        //para los usuarios sin iniciar sesion
        if (!$currentUser) {
            throw $this->createAccessDeniedException('Debes estar logueado');
        }

        $token = $request->request->get('_token');


        if ($this->isCsrfTokenValid('follow' . $id, $token)) {
            $em = $this->getDoctrine()->getManager();
            $usuarioRepository = $em->getRepository('App:Usuario');

            $usuarioASeguir = $usuarioRepository->find($id);

            if (!$usuarioASeguir) {
                throw $this->createNotFoundException('Usuario no encontrado');
            }

            // Crear nueva relación de seguidor
            $seguidor = new Seguidor();
            $seguidor->setFollower($currentUser);
            $seguidor->setUsuario($usuarioASeguir);

            $em->persist($seguidor);
            $em->flush();
        }

        // Redirigir de vuelta a la página del usuario
        return $this->redirectToRoute('detallesUser', ['coordinador' => $usuarioASeguir->getNombre()]);
    }

    /**
     * @Route("/dejarDeSeguir/{id}", name="dejarDeSeguir", methods={"POST"})
     * */

    public function dejarDeSeguir(
        $id,
        Request $request,
        SeguidorRepository $seguidorRepository
    ): Response {
        $currentUser = $this->getUser();

        if (!$currentUser) {
            throw $this->createAccessDeniedException('Debes estar logueado');
        }

        $token = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $id, $token)) {
            $em = $this->getDoctrine()->getManager();
            $seguidor = $seguidorRepository->findOneBy([
                'follower' => $currentUser->getId(),
                'usuario' => $id
            ]);

            if ($seguidor) {
                $em->remove($seguidor);
                $em->flush();
                $this->addFlash('success', 'Has dejado de seguir al usuario.');
            }
        }

        return $this->redirectToRoute('perfil', ['id' => $currentUser->getId()]);
    }


    /**
     * @Route("/confirmacion", name="confirmacion", methods={"GET","POST"})
     * */
    public function confirmacion(Request $request, MailerInterface $mailer)
    {
        $form = json_decode($request->query->get('formData'), true);
        $entityManager = $this->getDoctrine()->getManager();
        $sesion = $entityManager->getRepository('App:Sesion')->find($form['sesion_id']);

        // Mostrar datos del formulario
        if ($request->isMethod('POST')) {           
            // se crea un pedido 
            $pedido = new Pedido();
            $pedido->setUsuario($this->getUser());
            $pedido->setSesion($sesion);
            $pedido->setEntradas($form['entradas']);
            $pedido->setFecha(new \DateTime());
            $usuario = $this->getUser();

            $entradasRestantes = $sesion->getEntradas() - $form['entradas'];
            if ($entradasRestantes < 0) {
                throw new \Exception('No hay suficientes entradas disponibles');
            }
            $sesion->setEntradas($entradasRestantes);


            $entityManager->persist($pedido);
            $entityManager->persist($sesion);
            $entityManager->flush();

            //Enviamos un correo
            try {
                $email = (new TemplatedEmail())
                    ->from(new Address('desplieguea@gmail.com', 'Participacion en ' . $sesion->getActividad()->getNombre()))
                    ->to($usuario->getCorreo())
                    ->subject($sesion->getActividad()->getNombre())

                    ->htmlTemplate('usuario/entradas/email_confirmacion.html.twig')
                    ->context([
                        'pedido' => $pedido,
                        'usuario' => $usuario
                    ]);
                $mailer->send($email);
            } catch (\Exception $e) {
                $this->addFlash('error', 'No se pudo enviar el email de confirmación, pero tu pedido se ha registrado');
            }
            return $this->redirectToRoute('historial', [
                'id' => $this->getUser()->getId(),
                
            ]);
        }


        return $this->render('usuario/entradas/confirmacion.html.twig', [
            'form' => $form,
            'sesion' => $sesion,
        ]);
    }
    
    /**
     * @Route("/devolucion/{id}", name="devolucion", methods={"POST"})
     */
    public function devolucion(
        $id,
        EntityManagerInterface $entityManager,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        MailerInterface $mailer
    ): RedirectResponse {
        // Validar el token CSRF
        $token = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('devolucion' . $id, $token))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }
        $usuario = $this->getUser();

        $pedido = $entityManager->getRepository(Pedido::class)->find($id);


        $entradas = (int) $request->request->get('entradas');
        $motivo = (string) $request->request->get('motivo');
        if (empty($motivo)) {
            $motivo = 'sin motivo';
        }

        // Crear una nueva devolución
        $devolucion = new Devolucion();
        $devolucion->setEntradas($entradas);
        $devolucion->setPedido($pedido);
        $devolucion->setFecha(new \DateTime());
        $devolucion->setMotivo($motivo);

        $entityManager->persist($devolucion);

        // Sumar entradas devueltas a la sesión
        $sesion = $pedido->getSesion();
        $sesion->setEntradas($sesion->getEntradas() + $entradas);

        // Actualizar las entradas restantes en el pedido
        $pedido->setEntradas($pedido->getEntradas() - $entradas);
        $entityManager->flush();

        //enviamos un correo
        try {
            $email = (new TemplatedEmail())
                ->from(new Address('desplieguea@gmail.com', 'Devolucion de entradas para ' . $sesion->getActividad()->getNombre()))
                ->to($usuario->getCorreo())
                ->subject($sesion->getActividad()->getNombre())

                ->htmlTemplate('usuario/entradas/email_devolucion.html.twig')
                ->context([
                    'pedido' => $pedido,
                    'devolucion' => $devolucion,
                    'usuario' => $usuario
                ]);
            $mailer->send($email);
        } catch (\Exception $e) {
            $this->addFlash('error', 'No se pudo enviar el email de devolución, pero tu pedido se ha registrado correctamente.');
        }

        return $this->redirectToRoute('historial', ['id' => $this->getUser()->getId()]);
    }

    /**
     * @Route("/{id}/misActividades", name="misActividades")
     * */
    public function misActividades($id, Request $request, SessionInterface $session)
    {
        $usuario = $this->getUser();

        $actCoord = [];
        $actividadesSesiones = [];
        $pedidosPorSesion = []; 
        $devolucionesPorPedido = [];

        $entityManager = $this->getDoctrine()->getManager();
        $repositorioAct = $entityManager->getRepository('App:Actividad');
        $repositorioSes = $entityManager->getRepository('App:Sesion');
        $repositorioPed = $entityManager->getRepository('App:Pedido');
        $repositorioDev = $entityManager->getRepository('App:Devolucion');
        $actCoord = $repositorioAct->findBy(['coordinador' => $id], ['id' => 'DESC']);

        //Contamos las entradas inicialal entes de participar en la sesion y recopilamos las actividades y sesions del usuario
        $totalEntradasIniciales = [];
        //recopilamos las actividades
        foreach ($actCoord as $actividad) {
            $sesiones =  $repositorioSes->findBy(['actividad' => $actividad]);

            $actividadesSesiones[$actividad->getId()] = $sesiones;

            //recopilamos las sesiones
            foreach ($sesiones as $sesion) {
                $pedidos = $repositorioPed->findBy(['sesion' => $sesion->getId()]);

                $pedidosPorSesion[$sesion->getId()] = $pedidos;

                $totalEntradasSesion = 0;
                $totalEntradasSesion += $sesion->getEntradas();

                //recopilamos los pedidos
                foreach ($pedidos as $pedido) {
                    $devoluciones = $repositorioDev->findBy(['pedido' => $pedido->getId()]);

                    $devolucionesPorPedido[$pedido->getId()] = $devoluciones;

                    $totalEntradasSesion += $pedido->getEntradas();
                    
                }
                $totalEntradasIniciales[$sesion->getId()] = $totalEntradasSesion;
            }
        }


        // Verificar si está vetado y obtener el motivo
        $vetados = $session->get('vetar', []);
        $mensajeVetado = null;

        // Si el id del usuario está en el array vetados, obtener el motivo
        foreach ($vetados as $veto) {
            // Verifica si el veto es un array y si su clave 'id' coincide con el ID
            if (is_array($veto) && isset($veto['id']) && $veto['id'] == $id) {
                $motivoVeto = $veto['motivo'];

                // Mensaje con el motivo y la fecha
                $mensajeVetado = "Estás vetado y no puedes realizar más actividades. Motivo: " . $motivoVeto;
                break;
            }
        }


        // Verificar si está prohibido y obtener el motivo
        $cookieName = 'prohibido_' . $id;
        $prohibido = $request->cookies->get($cookieName);
        $mensajeProhibido = null;

        if ($prohibido) {
            $prohibidoData = json_decode($prohibido, true);
            // Obtenemos el motivo de la prohibición
            $motivo = $prohibidoData['motivo'];

            // Calculamos la fecha de expiración
            $expirationTime = time() + 604800;
            $expirationDate = new DateTime("@$expirationTime");
            $expirationDateFormatted = $expirationDate->format('d-m-Y');

            // Mensaje con el motivo y la fecha
            $mensajeProhibido = "No puedes publicar nuevas actividades hasta el " . $expirationDateFormatted .
                ". Motivo: " . $motivo;
        }



        return $this->render('usuario/misActividades.html.twig', [
            'actividades' => $actCoord,
            'actividadesSesiones' => $actividadesSesiones,
            'pedidosPorSesion' => $pedidosPorSesion,
            'devolucionesPorPedido' => $devolucionesPorPedido,
            'totalEntradasIniciales' => $totalEntradasIniciales,
            'usuario' => $usuario,
            'mensajeVetado' => $mensajeVetado,
            'mensajeProhibido' => $mensajeProhibido
        ]);
    }


    /**
     * @Route("/historial/{id}", name="historial")
     * */
    public function historial($id)
    {
        $pedidos = [];
        $devoluciones = [];

        $entityManager = $this->getDoctrine()->getManager();
        $repositorioPed = $entityManager->getRepository('App:Pedido');
        $repositorioDev = $entityManager->getRepository('App:Devolucion');
        $pedidos = $repositorioPed->findBy(['usuario' => $id], ['id' => 'DESC']);
        //recopila las devoluciones de pedidos del usuario
        foreach ($pedidos as $pedido) {
            $devolucionesPorPedido = $repositorioDev->findBy(['pedido' => $pedido->getId()]);
            $devoluciones[$pedido->getId()] = $devolucionesPorPedido;
        }
        return $this->render('usuario/historial.html.twig', [
            'pedidos' => $pedidos,
            'devoluciones' => $devoluciones,
        ]);
    }



    /**
     * @Route("/publicarActividad/{id}", name="publicarActividad", methods={"POST"})
     */
    public function publicarActividad($id, EntityManagerInterface $entityManager, Request $request, CsrfTokenManagerInterface $csrfTokenManager): RedirectResponse
    {
        $actividad = $entityManager->getRepository(Actividad::class)->find($id);

        if (!$actividad) {
            throw $this->createNotFoundException('Actividad no encontrada');
        }

        $token = $request->request->get('_token');

        if ($csrfTokenManager->isTokenValid(new CsrfToken('publicar' . $actividad->getId(), $token))) {
            // Cambiar el estado de la actividad a "finalizado"
            $actividad->setEstado("publicado");
            $entityManager->persist($actividad);
            $entityManager->flush();

            $coordinadorId = $actividad->getCoordinador()->getId();
            return $this->redirectToRoute('misActividades', ['id' => $coordinadorId]);
        } else {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }
    }




    /**
     * @Route("/finalizarActividad/{id}", name="finalizarActividad", methods={"POST"})
     */
    public function finalizarActividad($id, EntityManagerInterface $entityManager, Request $request, CsrfTokenManagerInterface $csrfTokenManager): RedirectResponse
    {
        $actividad = $entityManager->getRepository(Actividad::class)->find($id);

        if (!$actividad) {
            throw $this->createNotFoundException('Actividad no encontrada.');
        }

        $token = $request->request->get('_token');

        if ($csrfTokenManager->isTokenValid(new CsrfToken('finalizar' . $actividad->getId(), $token))) {
            // Cambiar el estado de la actividad a "finalizado"
            $actividad->setEstado("finalizado");
            $entityManager->persist($actividad);
            $entityManager->flush();

            $coordinadorId = $actividad->getCoordinador()->getId();
            return $this->redirectToRoute('misActividades', ['id' => $coordinadorId]);
        } else {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }
    }

    /**
     * @Route("/eliminarActividad/{id}", name="eliminarActividad", methods={"POST"})
     * */

    public function eliminarActividad(EntityManagerInterface $entityManager, Actividad $actividad, Request $request, CsrfTokenManagerInterface $csrfTokenManager): RedirectResponse
    {

        $token = $request->request->get('_token');

        if ($csrfTokenManager->isTokenValid(new CsrfToken('delete' . $actividad->getId(), $token))) {
            $coordinadorId = $actividad->getCoordinador()->getId();

            $direccion = $actividad->getDireccion();
            if ($direccion !== null) {
                // Elimina la dirección asociada
                $entityManager->remove($direccion);
            }
            // Elimina la actividad
            $entityManager->remove($actividad);
            $entityManager->flush();

            return $this->redirectToRoute('misActividades', ['id' => $coordinadorId]);
        } else {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }
    }


    /**
     * @Route("/nueva-direccion", name="nueva_direccion", methods={"POST","GET"})
     */
    public function nuevaDireccion(Request $request, EntityManagerInterface $entityManager): Response
    {

        $form = $this->createForm(CrearDireccionType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('sin_direccion')->getData()) {
                return $this->redirectToRoute('nueva_actividad');
            } else {

                $formData = [
                    'pais' => $form->get('pais')->getData(),
                    'provincia' => $form->get('provincia')->getData(),
                    'ciudad' => $form->get('ciudad')->getData(),
                    'codpostal' => $form->get('codpostal')->getData(),
                    'calle' => $form->get('calle')->getData(),
                    'numero' => $form->get('numero')->getData(),
                    'piso' => $form->get('piso')->getData() ?? null,
                ];
                //si faltan los campos necesarios se mostrara el siguiente mensaje
                if (empty($formData['provincia']) || empty($formData['ciudad']) || empty($formData['codpostal']) || empty($formData['calle']) || empty($formData['numero'])) {
                    $this->addFlash('errorDireccion', 'Por favor, completa los campos obligatorios');
                    return $this->render('usuario/actividad/añadir/direccion.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
                //Codificamos la informacion si hay direccion y mandamos
                return $this->redirectToRoute('nueva_actividad', [
                    'direccionData' => json_encode($formData),
                ]);
            }
        }

        return $this->render('usuario/actividad/añadir/direccion.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/nueva-actividad", name="nueva_actividad", methods={"GET", "POST"})
     */
    public function nuevaActividad(Request $request, EntityManagerInterface $entityManager, $direccionData = null): Response
    {
        $em = $this->getDoctrine()->getManager();
        $formData = $request->query->get('direccionData');

        $direccion = null;


        $tipo = $direccionData === null && !$formData ? 'online' : 'presencial';
        $user = $this->getUser();
        $usuario = $em->getRepository('App:Usuario')->find($user->getId());

        //creamos la actividad
        $actividad = new Actividad();
        $form = $this->createForm(ActividadType::class, $actividad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Si se envio los datos de una direccion desde 'nueva_direccion' se inserta en la BD
            if ($formData) {
                //descodificamos la informacion de la direccion
                $formData = json_decode($formData, true);

                //creamos una nueva direccion
                $direccion = new Direccion();
                $direccion->setPais($formData['pais']);
                $direccion->setProvincia($formData['provincia']);
                $direccion->setCiudad($formData['ciudad']);
                $direccion->setCodpostal($formData['codpostal']);
                $direccion->setCalle($formData['calle']);
                $direccion->setNumero($formData['numero']);
                $direccion->setPiso($formData['piso'] ?? null);
                $entityManager->persist($direccion);
            }
            $actividad->setDireccion($direccion);
            $actividad->setCoordinador($usuario);
            $actividad->setTipo($tipo);
            $actividad->setEstado('desarrollo');
            $entityManager->persist($actividad);
            $entityManager->flush();

            return $this->redirectToRoute('nueva_sesion', ['actividadId' => $actividad->getId()]);
        }


        return $this->render('usuario/actividad/añadir/actividad.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/nueva-sesion/{actividadId}", name="nueva_sesion", methods={"GET", "POST"})
     */
    public function nuevaSesion(Request $request, EntityManagerInterface $entityManager, $actividadId): Response
    {
        $actividad = $entityManager->getRepository(Actividad::class)->find($actividadId);

        if (!$actividad) {
            throw $this->createNotFoundException('La actividad solicitada no existe.');
        }

        $repositorioSes = $entityManager->getRepository('App:Sesion');
        $dataSesiones = $repositorioSes->findBy(['actividad' => $actividad]);

        $user = $this->getUser();
        $coordinadorId =  $user->getId();


        $sesion = new Sesion();
        $form = $this->createForm(CrearSesionType::class, $sesion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sesion->setActividad($actividad);
            $entityManager->persist($sesion);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('nueva_sesion', ['actividadId' => $actividadId]));
        }

        return $this->render('usuario/actividad/añadir/sesion.html.twig', [
            'form' => $form->createView(),
            'coordinadorId' => $coordinadorId,
            'sesiones' => $dataSesiones,

        ]);
    }


    /**
     * @Route("/eliminarSesion/{id}", name="eliminarSesion", methods={"POST"})
     * */

    public function eliminarSesion(EntityManagerInterface $entityManager, Sesion $sesion, Request $request, CsrfTokenManagerInterface $csrfTokenManager): RedirectResponse
    {

        $token = $request->request->get('_token');

        if ($csrfTokenManager->isTokenValid(new CsrfToken('delete' . $sesion->getId(), $token))) {
            $entityManager->remove($sesion);
            $entityManager->flush();

            $refererUrl = $request->headers->get('referer');
            return $this->redirect($refererUrl);
        } else {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }
    }


    /**
     * @Route("/actualizar-direccion/{actividadId}/{direccionId}", name="actualizar_direccion", methods={"POST", "GET"})
     */

    public function actualizarDireccion($direccionId, $actividadId, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Si `id` es 0, creamos una nueva dirección; si no, buscamos la existente
        $direccion = ($direccionId == 0) ? new Direccion() : $entityManager->getRepository(Direccion::class)->find($direccionId);

        //al crear el form le pasamos el valor de direccion si es = a 0 semarcara la casilla 'sin_direccion' del form
        $form = $this->createForm(DireccionType::class, $direccion, [
            'sin_direccion' => $direccionId == 0,
        ]);
        $form->handleRequest($request);

        $actividad = $entityManager->getRepository(Actividad::class)->findOneBy(['id' => $actividadId]);


        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('sin_direccion')->getData()) {

                $actividad->setTipo('online');
                $actividad->setDireccion(null);
                $entityManager->persist($actividad);
                $entityManager->flush();


                // eliminamos la dirección
                if ($direccionId != 0) {
                    $entityManager->remove($direccion);
                    $entityManager->flush();
                }
                return $this->redirectToRoute('actualizar_direccion', ['actividadId' => $actividadId, 'direccionId' => 0]);
            } else {
                // Si hay datos de dirección, actualizamos la actividad y la dirección
                $entityManager->persist($direccion);
                $actividad->setTipo('presencial');
                $actividad->setDireccion($direccion);
                $entityManager->persist($actividad);
                // Guardamos
                $entityManager->flush();
                return $this->redirectToRoute('actualizar_direccion', ['actividadId' => $actividadId, 'direccionId' => $direccion->getId()]);
            }
        }

        return $this->render('usuario/actividad/actualizar/direccion.html.twig', [
            'form' => $form->createView(),
            'direccion' => $direccion,
            'actividad' => $actividad,
        ]);
    }

    /**
     * @Route("/actualizar-actividad/{actividadId}", name="actualizar_actividad", methods={"POST", "GET"})
     */

    public function actualizarActividad($actividadId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $actividad =  $entityManager->getRepository(Actividad::class)->find($actividadId);

        $form = $this->createForm(ActividadType::class, $actividad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($actividad);
            $entityManager->flush();
            return $this->redirectToRoute('actualizar_actividad', ['actividadId' => $actividad->getId()]);
        }

        return $this->render('usuario/actividad/actualizar/actividad.html.twig', [
            'form' => $form->createView(),
            'direccion' => $actividad->getDireccion(),
            'actividad' => $actividad,
        ]);
    }


    /**
     * @Route("/actualizar-sesion/{actividadId}/{sesionId?}", name="actualizar_sesion", methods={"GET", "POST"})
     */
    public function actualizarSesion(Request $request, EntityManagerInterface $entityManager, $actividadId, $sesionId = null): Response
    {
        $actividad = $entityManager->getRepository(Actividad::class)->find($actividadId);

        if (!$actividad) {
            throw $this->createNotFoundException('La actividad no existe');
        }

        $repositorioSes = $entityManager->getRepository(Sesion::class);
        $dataSesiones = $repositorioSes->findBy(['actividad' => $actividad]);

        // Si se ha proporcionado una sesion existente busca esa sesión para actualizar sino crea una nueva
        $sesion = $sesionId ? $repositorioSes->find($sesionId) : new Sesion();

        if ($sesionId && !$sesion) {
            throw $this->createNotFoundException('La sesión no existe');
        }

        $form = $this->createForm(CrearSesionType::class, $sesion);
        $form->handleRequest($request);

        // Generar formularios para cada sesión existente
        $forms = [];
        foreach ($dataSesiones as $existingSesion) {
            $forms[$existingSesion->getId()] = $this->createForm(CrearSesionType::class, $existingSesion)->createView();
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $sesion->setActividad($actividad);
            $entityManager->persist($sesion);
            $entityManager->flush();

            return $this->redirectToRoute('actualizar_sesion', ['actividadId' => $actividadId]);
        }

        return $this->render('usuario/actividad/actualizar/sesion.html.twig', [
            'form' => $form->createView(),
            'sesiones' => $dataSesiones,
            'sessionForms' => $forms,
            'direccion' => $actividad->getDireccion(),
            'actividad' => $actividad,
        ]);
    }


    /**
     * @Route("/asistencia", name="asistencia")
     */
    public function asistencia(Request $request, MailerInterface $mailer)
    {

        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $usuario = $em->getRepository('App:Usuario')->find($user->getId());
        $form = $this->createForm(AsistenciaType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $receptor = $form->get('receptor')->getData();
            $asunto = $form->get('asunto')->getData();
            $mensaje = $form->get('mensaje')->getData();


            $email = (new TemplatedEmail())
                ->from(new Address('desplieguea@gmail.com', 'Asistencia al Usuario'))
                ->to($receptor)
                ->subject($asunto)
                ->text($mensaje)
                ->replyTo(new Address($usuario->getCorreo(), $usuario->getNombre()));

            $mailer->send($email);
            $this->addFlash('enviado', 'Correo enviado!');
            return $this->render('usuario/asistencia.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        return $this->render('usuario/asistencia.html.twig', [
            'form' => $form->createView()
        ]);
    }


/**
     * @Route("/cancelarActividad/{id}", name="cancelarActividad", methods={"GET", "POST"})
     */
public function cancelarActividad($id, EntityManagerInterface $entityManager, Request $request, CsrfTokenManagerInterface $csrfTokenManager, MailerInterface $mailer, SessionInterface $session): RedirectResponse 
{
    $actividad = $entityManager->getRepository(Actividad::class)->find($id);

    if (!$actividad) {
        throw $this->createNotFoundException('Actividad no encontrada');
    }

    $token = $request->request->get('_token');

    if ($csrfTokenManager->isTokenValid(new CsrfToken('cancelar' . $actividad->getId(), $token))) {
        // Cambiar el estado de la actividad a "cancelado"
        $actividad->setEstado("cancelado");
        $entityManager->persist($actividad);
        $entityManager->flush();

        // Obtener sesiones asociadas a la actividad
        $sesiones = $entityManager->getRepository(Sesion::class)->findBy(['actividad' => $actividad]);

        // Obtener pedidos asociados a las sesiones
        $pedidos = $entityManager->getRepository(Pedido::class)->findBy(['sesion' => $sesiones]);
     

        // Enviar un correo a cada usuario
        foreach ($pedidos as $pedido) {
            $usuario = $pedido->getUsuario(); 
            $motivo= "Cancelacion por problemas con la actividad";
            $devolucion = new Devolucion();
            $devolucion->setEntradas($pedido->getEntradas());
            $devolucion->setPedido($pedido);
            $devolucion->setFecha(new \DateTime());
            $devolucion->setMotivo($motivo);
    
            $entityManager->persist($devolucion);
    
            // Sumar entradas devueltas a la sesión
            $sesion = $pedido->getSesion();
            $sesion->setEntradas($sesion->getEntradas() + $pedido->getEntradas());
    
            // Actualizar las entradas restantes en el pedido
            $pedido->setEntradas($pedido->getEntradas() - $pedido->getEntradas());
            $entityManager->flush();

       
            if ($usuario && $usuario->getCorreo()) {
                $email = (new TemplatedEmail())
                    ->from(new Address('desplieguea@gmail.com', 'Atención al Usuario'))
                    ->to($usuario->getCorreo())
                    ->subject('ActIVAT cancelación de ' . $actividad->getNombre())
                    ->htmlTemplate('usuario/entradas/email_cancelacion.html.twig')
                    ->context([
                        'usuario' => $usuario,
                        'pedido' => $pedido
                    ]);

                $mailer->send($email);
            }
        }

       
        

  // Verifica si el path es 'detallesAct'
  if ($request->getPathInfo() === '/detallesAct') {
        //Creamos un registro para la session de los registros de administradores
        $registroAdmin = [
            'adminId' => $this->getUser()->getId(),
            'usuario' => $actividad ->getCoordinador() ->getId(),
            'accion' => 'Cancelar actividad de id:'.$actividad ->getId().' y nombre '.$actividad ->getNombre(),
            'motivo' => $request->request->get('motivo', 'Motivo no especificado'),
            'fecha' => new \DateTime()
        ];

        // Obtener registros existentes de la sesión y lo añadimos e actualizamos
        $registrosAdmins = $session->get('registros_admins', []);
        $registrosAdmins[] = $registroAdmin;
        $session->set('registros_admins', $registrosAdmins);
    }

        return $this->redirectToRoute('index');
    } else {
        throw $this->createAccessDeniedException('Invalid CSRF token');
    }
}

}
