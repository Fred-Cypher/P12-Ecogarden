<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class JWTExceptionListener
{
    public function onJWTNotFound (JWTNotFoundEvent $event): void
    {
        $data = [
            'status' => 401,
            'message' => 'Authentification requise : jeton JWT manquant',
        ];

        $event->setResponse(new JsonResponse($data, 401));
    }

    public function onJWTInvalide (JWTInvalidEvent $event): void
    {
        $data = [
            'status' => 401,
            'message' => 'Jeton JWT invalide ou expiré',
        ];

        $event->setResponse(new JsonResponse($data, 401));
    }

    public function onAuthenticationFailure(AuthenticationFailureEvent $event): void
    {
        $data = [
            'status' => 401,
            'message' => 'Echec de l\'authentification : identifiants incorrects',
        ];

        $event->setResponse(new JsonResponse($data, 401));
    }
}
