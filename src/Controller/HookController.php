<?php

namespace App\Controller;

use App\Message\WhatsappNotification;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\MessageService;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


class HookController extends AbstractController
{
    #[Route('/hook-endpoint', name: 'hook_endpoint')]
    // POST
    public function index(MessageBusInterface $bus, Request $request, LoggerInterface $logger): Response
    {

        $logger->info('Wooo');

        $content = $request->getContent();

        /*
         * Json:
         *
         * {
                "contacts": [
                    {
                        "profile": {
                            "name": "Ward"
                        },
                        "wa_id": "34622814642"
                    }
                ],
                "messages": [
                    {
                        "from": "34622814642",
                        "id": "ABGGNGIoFGQvAgo-sAr3kcI5DI30",
                        "text": {
                            "body": "Test from ward"
                        },
                        "timestamp": "1640174341",
                        "type": "text"
                    }
                ]
            }
         *
         *
         *
         */


        $bus->dispatch(new WhatsappNotification($content));

        return $this->json([
            'message' => 'Message sent!',
        ]);
    }


    #[Route('/email-xyx')]
    public function sendEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('it@gl-uniexco.com')
            ->to('wardazo@gmail.com')
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            dd($e->getMessage());
        }
        dd("Semt");
    }
}
