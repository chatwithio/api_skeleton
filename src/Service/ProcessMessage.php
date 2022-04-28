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

class ProcessMessage
{

    private $logger;

    private $em;

    private $service;

    private $mailer;

    private $sendmail;

    private $message = [
        'type' => null,
        'message' => null,
        'image_id' => null,
        'from' => null,
        'name' => null,
        'wa_id' => null,
        'timestamp' => null,
        'code' => null,
        'code2' => null,
        'mime' => null
    ];


    private $mess = [
        "S" => "He recibido el código gracias: ",
        "E" => "No hemos podido encontrar el código"
    ];

    private $oracle;

    private $hasError = false;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, MessageService $service, MailerInterface $mailer, OracleService $oracle)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->service = $service;
        $this->mailer = $mailer;
        $this->oracle = $oracle;
    }

    private function reset()
    {
        $this->message = [
            'type' => null,
            'message' => null,
            'image_id' => null,
            'from' => null,
            'name' => null,
            'wa_id' => null,
            'timestamp' => null,
            'code' => null,
            'code2' => null,
            'mime' => null
        ];
    }

    public function process($datas)
    {

        if(!isset($datas['messages'])){
            return;
        }

        foreach ($datas['messages'] as $k => $data) {

            if (!$this->extractMessageData($data)) {
                die("extraction failed");
            }

            $this->extractMetaData($datas['contacts'][$k]);

            if ($this->isHelp($this->message['message'])) {
                continue;
            }

            if ($this->isWarehouse()) {
                $this->processWarehouseMessage();
            } else {
                $this->processDeliveryMessage();
            }
            $this->reset();
        }
    }

    private function isHelp($s)
    {
        switch ($s) {
            case "INSTRUCCIONES":
            case "COMO ENVIAR":
            case "GUIA":
            case "AYUDA":
            case "HELP":
                $this->service->sendWhatsAppText(
                    $this->message['wa_id'],
                    "Para informar de entregas / Recogidas: Cuando este en almacén del Exportador (recogida), o una vez esté en el almacén del importador para hacer una entrega, hay que enviar el Num de Albaran, indicado arriba a mano derecha : ALBARAN TT :  XXXXXX - SOLO EL NUMERO,   NADA MAS"
                );
                return true;
            default:
                return false;
        }
        return false;
    }

    private function isWarehouse(): bool
    {
        if ($this->message['image_id']) {
            return true;
        } else if (preg_match('/^(FOTO|FOTOS) [0-9]{6,8}/i', $this->message['message'])) {
            return true;
        }
        return false;
    }


    private function extractMessageData($data): bool
    {
        $this->message['timestamp'] = $data['timestamp'];
        $this->message['from'] = $data['from'];

        if (isset($data['image'])) {

            $this->message['image_id'] = $data['image']['id'];

            $this->message['mime'] = $data['image']['mime_type'];

            if (!empty($data['image']['caption'])) {
                $this->message['message'] = $data['image']['caption'];
            }
            return true;
        }
        if (isset($data['text']['body'])) {
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

    private function processDeliveryMessage()
    {
        try {
            $this->message['code'] = $this->extractCode($this->message['message']);

            if (!$this->message['code']) {
                $this->validationError($this->mess["E"] .' '. $this->message['code']);
                return;
            }
            $message = new message();
            $message->setMessageFrom($this->message['from']);
            $message->setTextBody($this->message['message']);
            $message->setProfileName($this->message['name']);
            $message->setWaId($this->message['wa_id']);
            $message->setStatus("S");
            $message->setCode($this->message['code']);
            $message->setTimestamp($this->message['timestamp']);
            $message->setCreated(new \DateTime("now"));
            $this->em->persist($message);
            $this->em->flush();

            $this->service->sendWhatsAppText(
                $this->message['wa_id'], $this->mess["S"] . ': ' . $this->message['code']
            );
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $this->sendDeliveryEmail();
    }

    private function sendDeliveryEmail()
    {
        try {

            $email = (new Email())
                ->from('it@gl-uniexco.com')
                //->to('wardazo@gmail.com')
                ->to('transporte@gl-uniexco.com')
                ->subject($this->message['code'])
                ->text($this->message['message'])
                ->html('<p>' . $this->message['message'] . '</p>');
            $this->mailer->send($email);

        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function processWarehouseMessage()
    {

        if (!$this->validateWarehouseMessage()) {
            //$this->validationError('Este formato no es valido para el almacen');
            return;
        }

        //Have to whitelist
        $warehouse = $this->em->getRepository(WarehouseMessage::class)->getLastMessage($this->message['wa_id']);

        if ($this->message['code'] || $this->message['code2']) {
            $warehouse = new WarehouseMessage();
            $warehouse->setMessageFrom($this->message['from']);
            $warehouse->setTextBody($this->message['message']);
            $warehouse->setProfileName($this->message['name']);
            $warehouse->setWaId($this->message['wa_id']);
            $warehouse->setStatus('');
            $warehouse->setcode((string)$this->message['code']);
            $warehouse->setCode2($this->message['code2']);
            $warehouse->setTimestamp($this->message['timestamp']);
            $warehouse->setCreated(new \DateTime("now"));
            $this->em->persist($warehouse);
            $this->em->flush();
        }

        if ($warehouse && $this->message['image_id']) {
            $photo = new Photo();
            $photo->setWhatsappImageIdentifier($this->message['image_id']);
            $photo->setCreated(new \DateTime("now"));
            $photo->setMime($this->message['mime']);
            $this->em->persist($photo);
            $warehouse->addPhoto($photo);
            $this->em->flush();
            $this->sendWarehouseEmail($photo);
            return $photo;
        } else if($this->message['image_id']) {
            $this->validationError("No hemos encontrado ningún dato para esta imagen");
            return;
        }
        elseif($warehouse){
            $subject = $warehouse->getCode();
            if ($warehouse->getCode2()) {
                $subject = $subject . ' ' . $warehouse->getCode2();
            }
            $this->service->sendWhatsAppText($this->message['wa_id'], $subject.' recibido');
        }
        else{
            $this->validationError("No hemos podido procesar este dato");
            return;
        }
    }

    private function validateWarehouseMessage()
    {
        if(!$this->oracle->checkTel($this->message['from'])){
            $this->validationError("Este número no está autorizado para enviar fotos a nuestro sistema. Conctacte con el departamento de Informática de Globelink Uniexco en el caso de querer añadir este teléfono.");
            return false;
        }

        if (!$this->message['message'] && $this->message['image_id']) {
            return true;
        }
        else if (preg_match('/^(FOTOS|FOTO) ([0-9]{6,8})( [a-zA-Z0-9]{4}-?[a-zA-Z0-9]{6}\.?[a-zA-Z0-9])?$/i', $this->message['message'], $matches)) {

            $this->message['code'] = $matches[2];
            if (isset($matches[3])) {
                $this->message['code2'] = $this->conver($matches[3]);




                if(!$this->oracle->checkExpCont($this->message['code'], $this->message['code2'])){
                    $this->validationError("Uno de estos 2 códigos  no está en la BBDD");
                    return false;
                }

            }
            else{
                if(!$this->oracle->checkExp($this->message['code'])){
                    $this->validationError("Este código  no está en la BBDD");
                    return false;
                }
            }
            return true;
        }
        $this->validationError("Este formato no es válido para el almacén.");
        return false;
    }

    function conver($s){

        $s = trim(strtoupper($s));

        if(!str_contains($s,'-')){
            $s = substr_replace($s,'-',4,0);
        }

        if(!str_contains($s,'.')){
            $s = substr_replace($s,'.',11,0);
        }

        return $s;

    }


    private function sendWarehouseEmail($photo)
    {
        $identifier = $this->service->getMedia($photo->getWhatsappImageIdentifier());

        if (is_object($photo) && $identifier) {

            $mime = $photo->getMime();
            $expMime = explode("/", $mime);

            $subject = $photo->getWarehouseMessage()->getCode();
            if ($photo->getWarehouseMessage()->getCode2()) {
                $subject = $subject . ' ' . $photo->getWarehouseMessage()->getCode2();
            }

            $email = (new Email())
                ->from('it@gl-uniexco.com')
                ->to('j.ferres@gl-uniexco.com','almacen@gl-uniexco.com')
                //->to('wardazo@gmail.com')
                ->subject("FOTOS ".$subject." MÓVIL:".$this->message['from'])
                ->text($this->message['message'])
                ->html('<p>' . $this->message['message'] . '</p>')
                ->attach($identifier, 'imagen.' . $expMime[1], $mime);
            $this->mailer->send($email);

            $this->service->sendWhatsAppText($this->message['wa_id'], $subject.' recibido');

        }
    }

    private function validationError($message)
    {
        if ($_SERVER['APP_ENV'] == 'dev') {
            dd($message);
        }
        $this->service->sendWhatsAppText($this->message['wa_id'], $message);
    }

    private function extractCode($text)
    {
        if (preg_match("/\d{6,7}/", $text, $matches)) {
            if (!empty($matches[0])) {
                return $matches[0];
            }
        }
        return false;
    }

}
