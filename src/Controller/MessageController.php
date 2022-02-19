<?php

namespace App\Controller;

use App\Message\WhatsappNotification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\MessageService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;  
use Symfony\Component\HttpKernel\KernelInterface; 

class MessageController extends AbstractController
{
    
    public function __construct()
    {
        $this->messageService = new MessageService();
        $this->request = Request::createFromGlobals();
         
          
    }


    /**
     * @Route("/sendMessage", name="message", methods={"POST"})
     */
    public function sendMessage()
    {
        $sandBoxKey = $this->getParameter('whats-app-key')['API_KEY_360DEGREE'];

      if ($this ->request ->request ->has('to') || $this ->request ->query->has('to'))
        {
        $to = (string)$this->request->request->has('to') ? $this->request->request->get('to') : $this->request
                ->query
                ->get('to');
           
        }
        else
        {
             throw new Exception('to parameter is required');
        }

         if ($this ->request ->request ->has('message') || $this ->request ->query->has('message'))
        {
        $message = (string)$this->request->request->has('message') ? $this->request->request->get('message') : $this->request
                ->query
                ->get('message');
           
        }
        else
        {
             throw new Exception('message parameter is required');
        }


         $response = new JsonResponse();
        $sendMessage=$this->messageService->sendWhatsAppText($to,$message);
     
        
        if($sendMessage->meta->api_status=='stable')
        {

         return $response->setData(['message' => "Message Sent Successfully"]);
        }
        else
        {
          return $response->setData(['message' => "Something Went Wrong"]);  
        }
       
       
    }
}
