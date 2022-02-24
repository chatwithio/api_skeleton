<?php

// src/MessageHandler/SmsNotificationHandler.php
namespace App\MessageHandler;

use App\Message\WhatsappNotification;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Entity\Message;
use App\Service\MessageService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;

#[AsMessageHandler]
class WhatsappNotificationHandler
{


    private $logger;

    public function __construct(LoggerInterface $logger,EntityManagerInterface $em, MessageService $service)
    {
        $this->logger = $logger;
        $this->messageService = $service;
        $this->entityManager = $em;
    }

    public function __invoke(WhatsappNotification $message)
    {
        $this->logger->info("Message sent!");
    }

    public function saveMessageData()
    {
        try{
          $randomNumber = rand(100000,999999);
       

      $contact = '{"contact":{"profile":{"name":"ankiee"},"wa_id":"34622814642"},"message":{"from":"34622814642","id":"ABGGNGIoFGQvAgo-sAr3kcI5DI30","text":{"body":'.$randomNumber.'},"timestamp":"1640174341","type":"text"}}';

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

    
        return array('status'=>'true');
    }catch(Exception $e) {
      
      return  array('status' => 'false','message'=>$e->getMessage());
        
   
   }

    }


}
