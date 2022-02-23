<?php

namespace App\Controller;

use App\Message\WhatsappNotification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test', name: 'test')]
    // POST
    public function index(MessageBusInterface $bus): Response
    {

        /*
         * Json:
         *
         * "654765476547654",
        ["011111","Ward","$12.00","Macbook"],
        'order_sent',
        'en',
        'f6baa15e_fb52_4d4f_a5a0_cde307dc3a85'
         *
         *
         *
         *
         */




        $bus->dispatch(new WhatsappNotification('Whatsapp me!'));

        return $this->json([
            'message' => 'Message sent!',
        ]);
    }
}
