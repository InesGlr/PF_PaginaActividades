<?php

namespace App\Controller;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AutentificacionController extends AbstractController
{

  /**
   * @Route("auth/logup", name="logup")
   */
  public function logup(Request $request)
  {
    $em = $this->getDoctrine()->getManager();

    $usuario = new \App\Entity\Usuario();

    $form_usuarios = $this->createForm(\App\Form\LogUpType::class, $usuario);
    $form_usuarios->handleRequest($request);

    if ($form_usuarios->isSubmitted() && $form_usuarios->isValid()) {
        $usuario->setRol(['ROLE_USER']);
        $em->persist($usuario);
        $em->flush();

        return $this->render('usuario/autentificacion/logup.html.twig', [
            'form_usuarios' => $form_usuarios->createView(),
            'notificacion' => true, 
        ]);
    }

    return $this->render('usuario/autentificacion/logup.html.twig', [
      'form_usuarios' => $form_usuarios->createView(),
      'notificacion' => false, 

    ]);
  }


  /** 
   * @Route("auth/login", name="login")
   * */

  public function login(AuthenticationUtils $authenticationUtils)
  {
    // Obtenemos el error
    $error = $authenticationUtils->getLastAuthenticationError();
    $ultimoUser = $authenticationUtils->getLastUsername();

    return $this->render('usuario/autentificacion/login.html.twig', [
      'last_username' => $ultimoUser,
      'error' => $error,
    ]);
  }


}
