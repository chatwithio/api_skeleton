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

        $content = $request->getContent();

        $bus->dispatch(new WhatsappNotification($content));

        return $this->json([
            'message' => 'OK',
        ]);
    }


//    #[Route('/email-xyx')]
//    public function sendEmail(MailerInterface $mailer, MessageService $service): Response
//    {
//        $x = $service->getMedia('b112e761-9fdb-488c-80be-b2f3fbefa229');
//        dd($x);
//    }
}


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
        ,           {
                    "from":"34622814642",
                     "id":"ABGGNGIoFGQvAhAotUuO2TMwu2shZLdk2eza",
                     "image":{
                        "caption":"Image test",
                        "id":"b112e761-9fdb-488c-80be-b2f3fbefa229",
                        "mime_type":"image/jpeg",
                        "sha256":"577624e5830df61025568c16e53f5e58fc7bb02c37ef87017547c9ac2c526fc7"
                     },
                     "timestamp":"1647940273",
                     "type":"image"
                            ]
                        }
                    }
         *
         *
         *
         */
