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


     /**
     * @Route("/sendMessageTemplate", name="messageTemplate", methods={"POST"})
     */


     public function sendMessageTemplate()
     {


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


     if ($this ->request ->request ->has('placeholders') || $this ->request ->query->has('placeholders'))
        {
        $placeholders = (string)$this->request->request->has('placeholders') ? $this->request->request->get('placeholders') : $this->request
                ->query
                ->get('placeholders');
           
        }
        else
        {
             throw new Exception('placeholders parameter is required');
        }


      if ($this ->request ->request ->has('template') || $this ->request ->query->has('template'))
         {
        $template = (string)$this->request->request->has('template') ? $this->request->request->get('template') : $this->request
                ->query
                ->get('template');
           
         }
        else
         {
             throw new Exception('template parameter is required');
         }




            if ($this ->request ->request ->has('language') || $this ->request ->query->has('language'))
        {
        $language = (string)$this->request->request->has('language') ? $this->request->request->get('language') : $this->request
                ->query
                ->get('language');
           
        }
        else
        {
             throw new Exception('language parameter is required');
        }


              if ($this ->request ->request ->has('namespace') || $this ->request ->query->has('namespace'))
        {
        $namespace = (string)$this->request->request->has('namespace') ? $this->request->request->get('namespace') : $this->request
                ->query
                ->get('namespace');
           
        }
        else
        {
             throw new Exception('namespace parameter is required');
        }

        $response = new JsonResponse();
        $sendMessage=$this->messageService->sendWhatsApp($to, $placeholders, $template, $language, $namespace);

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
