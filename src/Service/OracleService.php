<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OracleService
{

    private $conn;

    private $logger;

    private $dev = false;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        if($_SERVER['APP_ENV'] == 'dev'){
            $this->dev = true;
        }
        else{
            $this->connect();
        }
    }

    private function connect(){
        try {
            $this->conn = oci_connect('axtra', 'axtra', '10.247.9.154/Axtra');
        }
        catch (\Exception $exception){
            $this->logger->error($exception->getMessage());
            die("Database not available");
        }
    }

    public function checkExp($xxxxxxx){
        if($this->dev) return true;
        $stid = oci_parse($this->conn, 'select * from uniexco.tt_t_gen where expediente='.$xxxxxxx);
        return $this->process($stid);
    }

    public function checkExpCont($xxxxxxx, $item){
        if($this->dev) return true;
        $stid = oci_parse(
            $this->conn,
            "SELECT * FROM UNIEXCO.TT_CTN WHERE EXPEDICION=$xxxxxxx AND upper(CONTENEDOR)=upper('$item')"
        );
        return $this->process($stid);
    }

    public function checkTel($tel){
        if($this->dev) return true;
        $stid = oci_parse($this->conn, "SELECT * FROM UNIEXCO.TOT_WHATSAPP_PERMISOS WHERE TELEFONO=$tel");
        return $this->process($stid);
    }

    private function process($stid){
        try{
            oci_execute($stid);

            if(oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)){
                return true;
            }
            return false;
        }
        catch (\Exception $exception){
            $this->logger->error($exception->getMessage());
            die("Database not available");
        }
    }
}
