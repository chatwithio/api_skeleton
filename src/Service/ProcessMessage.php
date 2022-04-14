<?php

namespace App\Service;


use App\Entity\Message;
use App\Entity\Photo;
use App\Entity\WarehouseMessage;
use App\Repository\WarehouseMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ProcessMessage{

    private $logger;

    private $em;

    private $service;

    private $mailer;

    private $sendmail;

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

    private $oracle;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, MessageService $service, MailerInterface $mailer, OracleService $oracle)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->service = $service;
        $this->mailer = $mailer;
        $this->oracle = $oracle;
    }

    private function reset(){
         $this->message = [
             'message'   => null,
             'image_id'  => null,
             'from'      => null,
             'name'      => null,
             'wa_id'     => null,
             'timestamp' => null,
             'code1'     => null,
             'code2' => null
         ];
    }

    public function process($datas){
        foreach ($datas['messages'] as $k => $data) {
            $this->reset();
            if(!$this->extractMessageData($data)){
                die("extraction failed");
            }

            $this->extractMetaData($datas['contacts'][$k]);

            dump($this->message);

            if($this->isWarehouse()){
                dump("warehouse");
                $this->processWarehouseMessage();
            }
            else{
                dump("external delivery");
                $photo = $this->processDeliveryMessage();

                $this->sendWarehouseEmail($photo);

            }
        }
    }

    private function isWarehouse(): bool
    {
        if($this->message['image_id']){
            return true;
        }
        else if(preg_match('/^FOTO(S) [0-9]{7,8}/i', $this->message['message'])){
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

            if(!$code){
                $this->validationError($this->mess["E"]." $code");
                return;
            }

            $message = new message();
            $message->setMessageFrom($this->message['from']);
            $message->setTextBody($this->message['message']);
            $message->setProfileName($this->message['name']);
            $message->setWaId($this->message['wa_id']);
            $message->setStatus("S");
            $message->setCode($code);
            $message->setTimestamp($this->message['timestamp']);
            $message->setCreated(new \DateTime("now"));
            $this->em->persist($message);
            $this->em->flush();

            $this->service->sendWhatsAppText(
                $this->message['wa_id'], $this->mess["S"].': '.$code
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

        if(!$this->validateWarehouseMessage()){
            $this->validationError('Este formato no es valido para el almacen');
            return;
        }

        //Have to whitelist
        $warehouse = $this->em->getRepository(WarehouseMessage::class)->getLastMessage($this->message['wa_id']);

        if($this->message['code1'] || $this->message['code2']){
            $warehouse = new WarehouseMessage();
            $warehouse->setMessageFrom($this->message['from']);
            $warehouse->setTextBody($this->message['message']);
            $warehouse->setProfileName($this->message['name']);
            $warehouse->setWaId($this->message['wa_id']);
            $warehouse->setStatus('');
            $warehouse->setCode1((string)$this->message['code1']);
            $warehouse->setCode2($this->message['code2']);
            $warehouse->setTimestamp($this->message['timestamp']);
            $warehouse->setCreated(new \DateTime("now"));
            $this->em->persist($warehouse);
        }
        else{
            //get last one
            $warehouse = $this->em->getRepository(WarehouseMessage::class)->getLastMessage($this->message['wa_id']);
        }

        if($warehouse){
            $photo = new Photo();
            $photo->setWhatsappImageIdentifier($this->message['image_id']);
            $this->em->persist($photo);
            $warehouse->addPhoto($photo);
            $this->em->flush();
            return $photo;
        }
        else{
            //send error message
        }
    }

    private function validateWarehouseMessage(){

        if(!$this->message['message'] && $this->message['image_id']){
            return true;
        }

        else if(preg_match('/^(FOTOS|FOTO) ([0-9]{7,8})( [a-zA-Z0-9]{4}-[a-zA-Z0-9]{6}\.[a-zA-Z0-9])?/i', $this->message['message'], $matches)){

            $this->message['code1'] = $matches[2];
            if(isset($matches[3])){
                $this->message['code2'] = trim($matches[3]);
            }
            return true;
        }
        return false;
    }

    private function sendWarehouseEmail($photo){
        if(is_object($photo) && $photo->getWhatsappImageIdentifier() && $photo->getCode){
            //send email
        }
    }

    private function validationError($message){
        if ($_SERVER['APP_ENV']=='dev'){
            dd($message);
        }
        $this->service->sendWhatsAppText($message);
    }

    private function extractCode($text){
        if (preg_match("/\d{6,7}/", $text, $matches)) {
            if (!empty($matches[0])) {
                return $matches[0];
            }
        }
        return false;
    }

//    private function emptyOrNewPhoto($warehouse, $code,$image){
//
//        $lastPhoto = $this->em->getRepository(Photo::class)->findOneBy([
//            "warehouseMessage" => $warehouse,
//        ],['id'=>'DESC']);
//
//        if(!$lastPhoto){
//            return new Photo();
//        }
//        else if($lastPhoto->getWhatsappImageIdentifier() && $lastPhoto->getCode()){
//            return new Photo();
//        }
//        else if($code && !$lastPhoto->getCode()){
//            //find an image without a code
//            return $lastPhoto;
//        }
//        elseif($image && !$lastPhoto->getWhatsappImageIdentifier()){
//            return $lastPhoto;
//        }
//        else{
//            //report orphan!
//        }
//    }

}
