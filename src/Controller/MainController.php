<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ModalidadType;
use App\Form\SesionType;
use App\Repository\ActividadRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\SesionRepository;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * */
    public function index(Request $request, SesionRepository $sesionRepository): Response
    {
        $form = $this->createForm(ModalidadType::class);
        $form->handleRequest($request);
    
        $actividades = [];
        $entityManager = $this->getDoctrine()->getManager();
        $repositorioAct = $entityManager->getRepository('App:Actividad');
        $repositorioSesion = $entityManager->getRepository('App:Sesion');
    
        // Comprobar sesiones pasadas y actualizar actividades
        $fechaActual = new \DateTime();
        $sesionesPasadas = $sesionRepository-> actividadesPasadas($fechaActual);
    
        $actividadesFinalizar = [];
    
        foreach ($sesionesPasadas as $sesion) {
            $actividad = $sesion->getActividad();
            $todasSesiones = $repositorioSesion->findBy(['actividad' => $actividad]);
    
            // Comprobar si todas las sesiones de esta dicha actividades están pasadas de fecha
            $todasPasadas = true;
            foreach ($todasSesiones as $sesionActividad) {
                // Combinar fecha y hora de la sesión antes de la comparación
                $fechaSesion = $sesionActividad->getFecha();
                $horaSesion = $sesionActividad->getHora();
                $fechaHoraSesion = new \DateTime($fechaSesion->format('Y-m-d') . ' ' . $horaSesion->format('H:i:s'));
            
                if ($fechaHoraSesion > $fechaActual) {
                    $todasPasadas = false;
                    break;
                }
            }
            
    
            // Si todas están pasadas se añade 
            if ($todasPasadas) {
                $actividadesFinalizar[] = $actividad;
            }
        }
    
        // Actualizamos el estado de las actividades pasadas de fecha a "finalizado"
        foreach ($actividadesFinalizar as $actividad) {
            $actividad->setEstado('finalizado');
            $entityManager->persist($actividad);
        }
        $entityManager->flush();
    
        // form del filtro de actividades
        if ($form->isSubmitted() && $form->isValid()) {
            $tipo = $form->get('tipo')->getData();
            $categoria = $form->get('categoria')->getData();
    
            $criterios = ['estado' => 'publicado'];
            if ($tipo) {
                $criterios['tipo'] = $tipo;
            }
            if ($categoria) {
                $criterios['categoria'] = $categoria;
            }
    
            $actividades = $repositorioAct->findBy($criterios);
        } else {
            $actividades = $repositorioAct->findBy(['estado' => 'publicado']);
        }
    
        return $this->render('main/index.html.twig', [
            'form' => $form->createView(),
            'actividades' => $actividades,
        ]);
    }


    /**
     * @Route("/coordinador/{coordinador}", name="detallesUser")
     * */

    public function detallesUser($coordinador, Request $request)
    {
        $actCoord = [];
        $entityManager = $this->getDoctrine()->getManager();
        $repositorioAct = $entityManager->getRepository('App:Actividad');
        $repositorioUser = $entityManager->getRepository('App:Usuario');

        $currentUser = $this->getUser();

        $coord = $repositorioUser->findOneBy(['nombre' => $coordinador]);

        if (!$coord) {
            throw $this->createNotFoundException('Usuario no encontrado');
        }

        // Verificar si el usuario sigue al coordinador
        $siguiendo = false;
        if ($currentUser) {
            $relacion = $entityManager->getRepository('App:Seguidor')->findOneBy([
                'follower' => $currentUser->getId(),
                'usuario' => $coord->getId()
            ]);
            $siguiendo = ($relacion !== null);
        }

        // Obtener las actividades 
        $actCoord = $repositorioAct->findBy(['coordinador' => $coord->getId()]);

        return $this->render('main/detallesUser.html.twig', [
            'coordinador' => $coord,
            'actividades' => $actCoord,
            'siguiendo' => $siguiendo,
            'currentUser' => $currentUser
        ]);
    }





    /**
     * @Route("/actividad/{id}", name="detallesAct")
     */
    public function detallesAct($id, Request $request, SessionInterface $session)
    {
        $user = $this->getUser();

        $entityManager = $this->getDoctrine()->getManager();

        $repositorioAct = $entityManager->getRepository('App:Actividad');
        $actividad = $repositorioAct->findOneBy(['id' => $id]);

        $repositorioSes = $entityManager->getRepository('App:Sesion');
        $dataSesiones = $repositorioSes->findBy(['actividad' => $actividad]);


        $repositorioPed = $entityManager->getRepository('App:Pedido');
        $repositorioDev = $entityManager->getRepository('App:Devolucion');

        $totalEntradasIniciales = [];
        foreach ($dataSesiones as $sesion) {
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

          


        // Creamos un form para cada sesión y almacenar en un array
        $formSesion = [];
        foreach ($dataSesiones as $sesion) {
            $form = $this->createForm(SesionType::class, $sesion);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $formData = [
                    'sesion_id' => $sesion->getId(),
                    'entradas' => $form->get('entradas')->getData()
                ];

                return $this->redirectToRoute('confirmacion', [
                    'formData' => json_encode($formData),
                ]);
            }

            $formSesion[$sesion->getId()] = $form->createView();
        }

       

        return $this->render('main/detallesAct.html.twig', [
            'actividad' => $actividad,
            'sesiones' => $dataSesiones,
            'formSesion' => $formSesion,
            'usuario' => $user,
            'totalEntradasIniciales' => $totalEntradasIniciales,
        ]);
    }



    /**
     * @Route("/search", name="search", methods={"POST", "GET"})
     */
    public function search(Request $request,  ActividadRepository $actividadRepository, PaginatorInterface $paginator)
    {
        $valor = $request->request->get('valor');
        if (empty($valor)) {
            return $this->redirectToRoute('index');
        }
        $actividades = $actividadRepository->busqueda($valor);
        $paginacion = $paginator->paginate(
            $actividades, //valor  
            $request->query->getInt('page', 1), // Num de página
            10 // Num de actividades por página
        );

        return $this->render('main/search.html.twig', [
            'actividades' => $paginacion,
            'valor' => $valor
        ]);
    }
}
