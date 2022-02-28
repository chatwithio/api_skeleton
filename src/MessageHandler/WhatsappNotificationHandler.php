<?php

// src/MessageHandler/SmsNotificationHandler.php
namespace App\MessageHandler;

use App\Message\WhatsappNotification;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Entity\Message;
use App\Service\MessageService;
use Doctrine\ORM\EntityManagerInterface;

#[AsMessageHandler]
class WhatsappNotificationHandler
{

    private $logger;

    private $em;

    private $service;

    private $mess = [
        "S" => "He recibido el código gracias: ",
        "E" => "No hemos podido encontrar el código"
    ];

    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, MessageService $service)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->service = $service;
    }

    public function __invoke(WhatsappNotification $message)
    {

        $datas = json_decode($message->getContent(), true);



        foreach ($datas['messages'] as $k => $data) {

            try {

                $code = $this->extractCode($data['text']['body']);

                if($code){
                    $status = "S";
                    $m = $this->mess[$status]." $code";
                }
                else{
                    $status = "E";
                    $m = $this->mess[$status];
                }

                $message = new message();
                $message->setMessageFrom($data['from']);
                $message->setTextBody($data['text']['body']);
                $message->setProfileName($datas['contacts'][$k]['profile']['name']);
                $message->setWaId($datas['contacts'][$k]['wa_id']);
                $message->setStatus($status);
                $message->setCode($code);
                $message->setTimestamp($data['timestamp']);
                $message->setCreated(new \DateTime("now"));
                $this->em->persist($message);
                $this->em->flush();

                $this->service->sendWhatsAppText(
                    $datas['contacts'][$k]['wa_id'], $m
                );

            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    private function extractCode($text){
        if (preg_match("/\d{6,7}/", $text, $matches)) {
            if (!empty($matches[0])) {
                return $matches[0];
            }
        }
        return false;
    }
}
