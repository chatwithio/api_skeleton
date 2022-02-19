<?php

namespace App\Service;

use App\Message\WhatsappNotification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;


class MessageService extends AbstractController
{


    private $senderEmail;

    //The api endpoints. There are more but these are the important ones
    private $endpoint = [
        'contact' => [
            'method' => 'POST',
            'url' => 'https://waba-sandbox.360dialog.io/v1/contacts'
        ],
        'message' => [
            'method' => 'POST',
            'url' => 'https://waba-sandbox.360dialog.io/v1/messages'
        ]
        ,
        'template' => [
            'method' => 'GET',
            'url' => 'https://waba-sandbox.360dialog.io/v1/configs/templates'
        ],
    ];

    //The header information. It contains the auth token too
    private $headers = [
        'Content-Type' => 'application/json',
        'D360-API-KEY' => null
    ];


    //False of the telephone number has KOed by whatsapp,
    //eg. if the user does not have whatsapp or it is a malformed number
    private $payloadOk = true;
   

    /**
     * WhatsApp360 constructor.
     */
    public function __construct()
    {
          
         $this->headers['D360-API-KEY'] =  $_ENV['WHATSAPP_KEY'];

        
       
    }

    /**
     * @param $endpoint (The endpoint to be used - see private $endpoint)
     * @param array $data
     * @return void (if there is any error an exception should be thrown)
     * @throws \GuzzleHttp\Exception\GuzzleException
     */

    private function send($endpoint, $data = [])
    {
       
        try {
            if ($this->payloadOk === true) {

                /*print_r($data);
                exit;*/
                $client = new Client;

                $request = $client->request(
                    $this->endpoint[$endpoint]['method'],
                    $this->endpoint[$endpoint]['url'],
                    [
                        "headers" => $this->headers,
                        "json" => $data
                    ]
                );

                if ($request->getStatusCode() == 200 || $request->getStatusCode() == 201) {
                    return json_decode($request->getBody()->getContents());
                } else {
                    throw new Exception($request->getBody()->getContents());
                }
            } else {
                throw new Exception('Unvalidated payload Exception');
            }
        } catch (Exception $exception) {
            $this->logErrors($exception);
        }
    }


    private function buildMessage($messageTemplate, $placeholders)
    {
        $counter = 1;
        foreach ($placeholders as $placeholder) {
            $messageTemplate = str_replace("{$counter}", $placeholder, $messageTemplate);
        }
        return $messageTemplate;
    }


    /**
     * @param $contact (whatapp number with no + or spacing)
     * @return bool (return true if the contact id OKed)
     * @throws \GuzzleHttp\Exception\GuzzleException
     */

    public function checkContact($contact)
    {
        try {
            $payload = [
                "blocking" => "wait",
                "contacts" => ["+" . $contact],
                "force_check" => true
            ];
           
          /* print_r($payload);
           exit;*/

            $response = $this->send('contact', $payload);

            if (!empty($response->contacts)) {
                return true;
            } else {
                return false;
            }
        }
        catch (Exception $exception) {
            $this->logErrors($exception);
        }
    }

    /**
     * @param $placeholders (an array of text only placeholders)
     * @return array
     */

    private function buildParams($placeholders){
        $arr = [];
        foreach($placeholders as $placeholder){
            $arr[] = [
                "type" => "text",
                "text" => $placeholder
            ];
        }
        return $arr;
    }

    /**
     * @param $to (whatapp number  - no spaces or = )
     * @param $placeholders array (of placeholders)
     * @param $template string (template name)
     * @param $language languale (ie en, must match the language of the approved template)
     * @param $namespace  string (template namespace  - you can get this from the getTemplates api)
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * NOTE, a template must be approved before it can be used
     */

    public function sendWhatsApp($to, $placeholders, $template, $language, $namespace)
    {
        $this->checkContact($to);
        $payload = [
            "to" => $to,
            "type" => "template",
            "template" => [
                "namespace" => $namespace,
                "language" => [
                    "policy" => "deterministic",
                    "code" => $language
                ],
                "name" => $template,
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => $this->buildParams($placeholders)

                    ]
                ]
            ]
        ];

        /*print_r($payload);
        exit;*/
        return $this->send('message', $payload);
    }

    /**
     *  @return array (list of templates  - with their nmespaces and approved status)
     */


    public function sendWhatsAppText($to,$message)
    {

       
        $payload = [
            "to" => $to,
            "type" => "text",
            "text"=>["body"=>$message]

        ];

        return $this->send('message', $payload);
    }


 

    public function getTemplates()
    {
        return $this->send('template');
    }

    private function logErrors(Exception $exception)
    {
        print "ERROR LOGGED:".$exception->getMessage()."\n";
    }
}


