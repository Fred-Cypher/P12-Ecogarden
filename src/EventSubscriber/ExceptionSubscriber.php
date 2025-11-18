<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onExceptionEvent(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        $status = 500;
        $message = 'Une erreur interne est survenue.';

        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
            $message = $exception->getMessage();

            if ($status === 400 || str_contains($message, 'Bad Request')) {
                $message = 'Demande mal formulée';
            }

            if ($status === 401 || str_contains($message, 'Unauthorized')) {
                $message = 'Authentification requise.';
            }


            if ($status === 403 || str_contains($message, 'Forbidden')) {
                $message = 'Accès refusé, page réservée aux administrateurs';
            }

            if ($status === 404) {
                $message = 'Ressource introuvable.';
            }
        }

        $data = [
            'status' => $status,
            'message' => $message,
        ];

        $response = new JsonResponse($data, $status);

        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => 'onExceptionEvent',
        ];
    }
}
