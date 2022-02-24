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
use App\Entity\Message;
use App\Service\MessageService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class HookController extends AbstractController
{



  public function __construct(EntityManagerInterface $em, MessageService $service)
    {
   $this->messageService = $service;
   $this->entityManager = $em;
  

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

        $randomNumber = rand(100000,999999);

      $contact = '{"contact":{"profile":{"name":"ward"},"wa_id":"34622814642"},"message":{"from":"34622814642","id":"ABGGNGIoFGQvAgo-sAr3kcI5DI30","text":{"body":'.$randomNumber.'},"timestamp":"1640174341","type":"text"}}';

      $data = json_decode($contact, true);
     /* print '<pre>';
      print_r($data);*/

      $text = $data['message']['text']['body'];
      $name =    $data['contact']['profile']['name'];
      $phoneNumber = $data['message']['from'];
      $time = $data['message']['timestamp'];
      $type = $data['message']['type'];
      $orderId = $data['message']['id'];



      $message = new message();
      $entityManager = $this->entityManager;
      $message->setName($name);
      $message->setMessage($text);
      $message->setPhoneNumber($phoneNumber);
      $message->setOrderId($orderId);
      $message->setCreatedAt($time);
      $message->setType($type);
      $entityManager->persist($message);
      $entityManager->flush();

      $this->messageService->sendWhatsAppText("34622814642","Hi there");

    

        $response = new JsonResponse();
        $response->setData(['data' => 'Data added successfully']);
        return $response;


    


    }


}
