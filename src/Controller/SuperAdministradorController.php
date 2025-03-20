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
 * @Route("/admin/super")
 */
class SuperAdministradorController extends AbstractController
{


    /**
     * @Route("/categorias", name="get_categorias")
     */
    public function getCategorias(EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $categorias = $entityManager->getRepository(Categoria::class)->findAll();

         //Obtenemos y guardamos los registros de acciones de los admin de su session
         $registrosSuperAdmins = $session->get('registros_superAdmins', []);

        return $this->render('superAdmin/lista.html.twig', [
            'categorias' => $categorias,
            'registrosSuperAdmins' => $registrosSuperAdmins

        ]);
    }

    /**
     * @Route("/crear-categoria", name="crear_categoria", methods={"GET", "POST"})
     */
    public function crearCategoria(Request $request, $id, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $categoria = new Categoria();
        $form = $this->createForm(CategoriaType::class, $categoria);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categoria);
            $entityManager->flush();


             //Creamos un registro para la session de los registros de administradores
        $registroSuperAdmin = [
            'adminId' => $this->getUser()->getId(),
            'categoria' => $id,
            'accion' => 'Crear categoria',
            'fecha' => new \DateTime()
        ];
        
        // Obtener registros existentes de la sesión y lo añadimos e actualizamos
        $registroSuperAdmins = $session->get('registros_admins', []);
        $registroSuperAdmins[] = $registroSuperAdmin;
        $session->set('registros_superAdmins', $registroSuperAdmins);

            return $this->redirectToRoute('get_categorias');
        }

        return $this->render('superAdmin/crear.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/editar-categoria/{id}", name="editar_categoria", methods={"GET", "POST"})
     */
    public function editarCategoria(Request $request, $id, Categoria $categoria, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $form = $this->createForm(CategoriaType::class, $categoria);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

                 //Creamos un registro para la session de los registros de administradores
        $registroSuperAdmin = [
            'adminId' => $this->getUser()->getId(),
            'categoria' => $id,
            'accion' => 'Editar categoria',
            'fecha' => new \DateTime()
        ];
        
        // Obtener registros existentes de la sesión y lo añadimos e actualizamos
        $registroSuperAdmins = $session->get('registros_admins', []);
        $registroSuperAdmins[] = $registroSuperAdmin;
        $session->set('registros_superAdmins', $registroSuperAdmins);

            return $this->redirectToRoute('get_categorias');
        }

        return $this->render('superAdmin/editar.html.twig', [
            'form' => $form->createView(),
            'categoria' => $categoria,
        ]);
    }
}
