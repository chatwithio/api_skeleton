<?php

// src/MessageHandler/SmsNotificationHandler.php
namespace App\MessageHandler;

use App\Message\WhatsappNotification;
use App\Service\ProcessMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Entity\Message;
use App\Service\MessageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


#[AsMessageHandler]
class WhatsappNotificationHandler
{
    private LoggerInterface $logger;

    private ProcessMessage $processMessage;

    public function __construct(LoggerInterface $logger, ProcessMessage $processMessage)
    {
        $this->logger = $logger;
        $this->processMessage = $processMessage;
    }

    public function __invoke(WhatsappNotification $message)
    {
        $datas = json_decode($message->getContent(), true);
        $this->processMessage->process($datas);
    }
}
