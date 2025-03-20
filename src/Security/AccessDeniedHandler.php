<?php


// src/Security/AccessDeniedHandler.php
namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler extends AbstractController implements AccessDeniedHandlerInterface
{
    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        // Enviar el código de error y la descripción a la plantilla
        return $this->render('error/error.html.twig', [
            'error_code' => 403,
            'error_message' => 'Acceso denegado: No tienes permisos suficientes para acceder a esta página.',
        ]);
    }
    
}
?>