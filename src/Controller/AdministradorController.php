<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UsuarioRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Cookie;
use App\Entity\Categoria;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\CategoriaType;

/**
 * @Route("/admin")
 * @IsGranted("ROLE_ADMIN", message="Debe Iniciar Sesion Para Continuar")
 */
class AdministradorController extends AbstractController
{

    /**
     * @Route("/getUsuario", name="getUsuario")
     */
    public function getUsuario(Request $request, UsuarioRepository $usuarioRepository, SessionInterface $session)
    {
        $em = $this->getDoctrine()->getManager();
        $valor = $request->query->get('valor');
        $vetados = $session->get('vetar', []);
        $vetadosIds = array_column($vetados, 'id');

        //Indexamos para poder obtener la información de la session 'vetar' 
        $vetadosIndexados = [];
        foreach ($vetados as $vetado) {
            if (isset($vetado['id'])) {
                $vetadosIndexados[$vetado['id']] = $vetado;
            }
        }

        //Comprobación de busqueda de usuario por el search
        if ($valor) {
            $listUsuarios = $usuarioRepository->busqueda($valor);
        } else {
            $listUsuarios = $em->getRepository('App:Usuario')->findBy([], ['nombre' => 'ASC']);
        }

        //Excluimos a los usuarios 'SUPER_ADMIN' del array
        $listUsuarios = array_filter($listUsuarios, function($usuario) {
            return !in_array('ROLE_SUPER_ADMIN', $usuario->getRol());
        });

        //Comprobacion de los usuarios que tengan una cookie 'prohibido'
        $prohibidos = [];
        foreach ($listUsuarios as $usuario) {
            $cookieName = 'prohibido_' . $usuario->getId();
            $cookie = $request->cookies->get($cookieName);

            
            if ($cookie) {
                //decodificamos la informacion de la cookie
                $dataDesfodificada = json_decode($cookie, true);
                if ($dataDesfodificada !== null) {
                    $prohibidos[(string)$usuario->getId()] = $dataDesfodificada; 
                }
            }
        }

        //Obtenemos y guardamos los registros de acciones de los admin de su session
        $registrosAdmins = $session->get('registros_admins', []);


        return $this->render('administrador/modUsuario.html.twig', [
            'listUsuarios' => $listUsuarios,
            'valor' => $valor,
            'vetadosIDs' => $vetadosIds,
            'vetados' => $vetadosIndexados,
            'prohibidos' => $prohibidos,
            'registrosAdmins' => $registrosAdmins
        ]);
    }


    /**
     * @Route("/crearAdmin/{id}", name="crearAdmin",  methods={"POST"})
     */
    public function crearAdmin(Request $request, $id, SessionInterface $session)
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('App:Usuario')->find($id);

        // Obtiene los roles actuales del usuario
        $rolesExistentes = $usuario->getRol(); 

        //Si no tiene 'ROLE_ADMIN' procede a convertir de string a array el 'rol' del usuario
        if (!in_array('ROLE_ADMIN', $rolesExistentes)) {
            $rolesExistentes[] = 'ROLE_ADMIN'; 
        }
        //Guardamos y enviamos a la BD
        $usuario->setRol($rolesExistentes);
        $em->persist($usuario);
        $em->flush();

        //Creamos un registro para la session de los registros de administradores
        $registroAdmin = [
            'adminId' => $this->getUser()->getId(),
            'usuario' => $id,
            'accion' => 'Crear Admin',
            'motivo' => $request->request->get('motivo', 'Motivo no especificado'),
            'fecha' => new \DateTime()
        ];
        
        // Obtener registros existentes de la sesión y lo añadimos e actualizamos
        $registrosAdmins = $session->get('registros_admins', []);
        $registrosAdmins[] = $registroAdmin;
        $session->set('registros_admins', $registrosAdmins);

        return $this->redirectToRoute('getUsuario');
    }

    /**
     * @Route("/eliminarAdmin/{id}", name="eliminarAdmin",  methods={"POST"})
     */
    public function eliminarAdmin(Request $request, $id, SessionInterface $session)
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('App:Usuario')->find($id);

        $rolesExistentes = $usuario->getRol();

        //Si tiene 'ROLE_ADMIN' procede a convertir de array a string el 'rol' del usuario
        if (in_array('ROLE_ADMIN', $rolesExistentes)) {
            $rolesExistentes = 'ROLE_USER';
        }

        //Guardamos y enviamos a la BD
        $usuario->setRol($rolesExistentes);
        $em->persist($usuario);
        $em->flush();

        //Creamos un registro para la session de los registros de administradores
        $registroAdmin = [
            'adminId' => $this->getUser()->getId(),
            'usuario' => $id,
            'accion' => 'Eliminar Admin',
            'motivo' => $request->request->get('motivo', 'Motivo no especificado'),
            'fecha' => new \DateTime()
        ];
        
        // Obtener registros existentes de la sesión y lo añadimos e actualizamos
        $registrosAdmins = $session->get('registros_admins', []);
        $registrosAdmins[] = $registroAdmin;
        $session->set('registros_admins', $registrosAdmins);

        return $this->redirectToRoute('getUsuario');
    }


   


    /**
     * @Route("/vetar/{id}", name="vetarUsuario", methods={"POST"})
     */
    public function vetarUsuario($id, Request $request, SessionInterface $session): Response
    {
        $motivo = $request->request->get('motivo');
        $vetados = $session->get('vetar', []);

        // Se almacena el motivo y el ID del usuario en la sesion y se guarda
        if (!in_array($id, array_column($vetados, 'id'))) {
            $vetados[] = ['id' => $id, 'motivo' => $motivo];
        }
        $session->set('vetar', $vetados);

        //Creamos un registro para la session de los registros de administradores
        $registroAdmin = [
            'adminId' => $this->getUser()->getId(),
            'usuario' => $id,
            'accion' => 'Vetar usuario',
            'motivo' => $request->request->get('motivo', 'Motivo no especificado'),
            'fecha' => new \DateTime()
        ];
        
        // Obtener registros existentes de la sesión y lo añadimos e actualizamos
        $registrosAdmins = $session->get('registros_admins', []);
        $registrosAdmins[] = $registroAdmin;
        $session->set('registros_admins', $registrosAdmins);

        return $this->redirectToRoute('getUsuario');
    }



    /**
     * @Route("/revertirVeto/{id}", name="revertirVetoUsuario")
     */
    public function revertirVetoUsuario($id, Request $request, SessionInterface $session): Response
    {
        $vetados = $session->get('vetar', []);

        foreach ($vetados as $key => $veto) {

            //Se localiza al usuario por su id en la session 
            if (is_array($veto) && isset($veto['id']) && $veto['id'] == $id) {
                unset($vetados[$key]);  // Se elimina
                break;  // Rompe el ciclo
            }
        }
        $session->set('vetar', $vetados);

        //Creamos un registro para la session de los registros de administradores
        $registroAdmin = [
            'adminId' => $this->getUser()->getId(),
            'usuario' => $id,
            'accion' => 'Revertir veto a usuario',
            'motivo' => $request->request->get('motivo', 'Motivo no especificado'),
            'fecha' => new \DateTime()
        ];
        
        // Obtener registros existentes de la sesión y lo añadimos e actualizamos
        $registrosAdmins = $session->get('registros_admins', []);
        $registrosAdmins[] = $registroAdmin;
        $session->set('registros_admins', $registrosAdmins);

        return $this->redirectToRoute('getUsuario');
    }

    /**
     * @Route("/prohibir/{id}", name="prohibirUsuario", methods={"POST"})
     */
    public function prohibirUsuario($id, Request $request,SessionInterface $session): Response
    {
        $motivo = $request->request->get('motivo'); // Obtener el motivo del form

        // nombramos la cookie y sus caracteristicas para el usuario prohibido
        $cookieName = 'prohibido_' . $id;
        $expirationTime = time() + 604800; // = a 7 días 

        //codificamos la informacion de la cookie
        $cookieValue = json_encode([
            'id' => $id,
            'motivo' => $motivo,
        ]);


        $registroAdmin = [
            'adminId' => $this->getUser()->getId(),
            'usuario' => $id,
            'accion' => 'Prohibir a usuario',
            'motivo' => $request->request->get('motivo', 'Motivo no especificado'),
            'fecha' => new \DateTime()
        ];
        
        $registrosAdmins = $session->get('registros_admins', []);
        $registrosAdmins[] = $registroAdmin;
        $session->set('registros_admins', $registrosAdmins);

        // Creamos y configuramos la cookie
        $cookie = new Cookie($cookieName, $cookieValue, $expirationTime);

        // redirijimos y añadimos la cookie
        $response = $this->redirectToRoute('getUsuario');
        $response->headers->setCookie($cookie);
        return $response;
    }

    /**
     * @Route("/revertirProhibicion/{id}", name="revertirProhibicionUsuario")
     */
    public function revertirProhibicionUsuario($id, Request $request, SessionInterface $session): Response
    {
        //buscamos la cookie
        $cookieName = 'prohibido_' . $id;

        //registraos la accion del admin
        $registroAdmin = [
            'adminId' => $this->getUser()->getId(),
            'usuario' => $id,
            'accion' => 'Revertir prohibición a usuario',
            'motivo' => $request->request->get('motivo', 'Motivo no especificado'),
            'fecha' => new \DateTime()
        ];
        
        $registrosAdmins = $session->get('registros_admins', []);
        $registrosAdmins[] = $registroAdmin;
        $session->set('registros_admins', $registrosAdmins);

        // redirijimos y eliminamos la cookie
        $response = $this->redirectToRoute('getUsuario');
        $response->headers->clearCookie($cookieName);

        return $response;
    }


}
