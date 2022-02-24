<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\MessageHandler\WhatsappNotificationHandler;


class HookController extends AbstractController
{



  public function __construct(WhatsappNotificationHandler $notificationHandler)
    {

   $this->notificationHandler = $notificationHandler;

  

    }


  #[Route('/hook-endpoint', name: 'hook_endpoint')]
    // POST
    public function index(MessageBusInterface $bus, MessageService $messageService): Response
    {

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

        dd($messageService->sendWhatsAppText("34622814642","Hi there"));

        $bus->dispatch(new WhatsappNotification('Whatsapp me!'));

        return $this->json([
            'message' => 'Message sent!',
        ]);
    }


    /**
     * @Route("/hook-url", name="hook_url", methods={"POST"})
     */
    public function saveMessageData(Request $request)
    {

        $saveData = $this->notificationHandler->saveMessageData();
  /*      print '<pre>';
        print_r($saveData);
        exit;*/
        if( $saveData['status']=="true")
        {
            $response = new JsonResponse();
            $response->setData(['message' => 'Data Saved Successfully']);
            return $response;
        }

    }


}
