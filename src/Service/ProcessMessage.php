<?php

namespace App\Service;


use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ProcessMessage{

    private $logger;

    private $em;

    private $service;

    private $mailer;


    private $message = [
        'type'      => null,
        'message'   => null,
        'image_id'  => null,
        'from'      => null,
        'name'      => null,
        'wa_id'     => null,
        'timestamp' => null
    ];



    private $mess = [
        "S" => "He recibido el código gracias: ",
        "E" => "No hemos podido encontrar el código"
    ];

    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, MessageService $service, MailerInterface $mailer)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->service = $service;
        $this->mailer = $mailer;
    }

    private function reset(){
         $this->message = [
             'message'   => null,
             'image_id'  => null,
             'from'      => null,
             'name'      => null,
             'wa_id'     => null,
             'timestamp' => null
         ];
    }

    public function process($datas){
        foreach ($datas['messages'] as $k => $data) {
            $this->reset();
            if(!$this->extractMessageData($data)){
                //send an error message
                die("extraction failed");
            }

            $this->extractMetaData($datas['contacts'][$k]);

            if(isWarehouse()){
                $this->processWarehouseMessage();
            }
            else{
                $this->processDeliveryMessage();
            }
        }
    }

    private function isWarehouse(): bool
    {
        if($this->image){
            return true;
        }
        else if(preg_match('/^FOTO(S) [0-9]{7,8}/')){
            return true;
        }
        return false;
    }


    private function extractMessageData($data): bool
    {

        $this->message['timestamp']= $data['timestamp'];
        $this->message['from']= $data['from'];

        if(isset($data['image'])){

            $this->message['image_id'] = $data['image']['id'];

            if(!empty($data['image']['caption'])){
                $this->message['message'] = $data['image']['caption'];
            }
            return true;
        }
        if(isset($data['text']['body'])){
            $this->message['message'] = $data['text']['body'];
            return true;
        }
        return false;
    }

    private function extractMetaData($data)
    {
        $this->message['name'] = $data['profile']['name'];
        $this->message['wa_id'] = $data['wa_id'];
    }



    private function processDeliveryMessage(){

        try {

            $code = $this->extractCode($this->message['message']);

            if($code){
                $status = "S";
                $m = $this->mess[$status]." $code";
            }
            else{
                $status = "E";
                $m = $this->mess[$status];
            }

            $message = new message();
            $message->setMessageFrom($this->message['form']);
            $message->setTextBody($this->message['message']);
            $message->setProfileName($this->message['name']);
            $message->setWaId($this->message['wa_id']);
            $message->setStatus($status);
            $message->setCode($code);
            $message->setTimestamp($this->message['timestamp']);
            $message->setCreated(new \DateTime("now"));
            $this->em->persist($message);
            $this->em->flush();

            $this->service->sendWhatsAppText(
                $this->message['wa_id'], $m
            );

        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $this->sendDeliveryEmail();
    }

    private function sendDeliveryEmail(){
        try {
            $email = (new Email())
                ->from('it@gl-uniexco.com')
                ->to('transporte@gl-uniexco.com')
                ->subject('Código enviado')
                ->text($this->message['message'])
                ->html('<p>'.$this->message['message'].'</p>');
            $this->mailer->send($email);

        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }



    private function processWarehouseMessage(){

    }

    private function sendWarehouseEmail(){

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
