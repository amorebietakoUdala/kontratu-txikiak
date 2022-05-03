<?php

namespace App\Service;

use App\Entity\Contract;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Twig\Environment;

class ContractNotifierService
{
   const RESPONSE_OK = '00000';

    private $client = null;
    private $twig = null;
    private $url;
    private $origin;
    private $adjudicator;
    private $entity;
    private $organ;
    private $powerType;
    private $mainActivity;
    private $soapUser;
    private $wsseUser;
    private $wssePassword;

    public function __construct(HttpClientInterface $client, Environment $twig, $url, $origin, $adjudicator, $entity, $organ, $powerType, $mainActivity, $soapUser, $wsseUser, $wssePassword) {
        $this->client = $client;
        $this->twig = $twig;
        $this->url = $url;
        $this->origin = $origin;
        $this->adjudicator = $adjudicator;
        $this->entity =  $entity;
        $this->organ = $organ;
        $this->powerType = $powerType;
        $this->mainActivity = $mainActivity;
        $this->soapUser = $soapUser;
        $this->wsseUser = $wsseUser;
        $this->wssePassword = $wssePassword;
    }

   /**
   * Create the body of the Web Service Request
   * 
   * @param Contract $contract
   * 
   * @return array<int,string>
   */
   public function notify(Contract $contract) {
      $result = [];
      if (null === $contract) {
         return $errors[] = "error.nullContract";
      } 
      try {
         $response = $this->client->request('POST',$this->url, [
             'headers' => [
                  'Accept-Encoding' => 'gzip,deflate',
                  'Content-Type' => 'text/xml;charset=UTF-8',
                  'SOAPAction' => '""',
                  'Connection' => 'Keep-Alive',
             ],
             'body' => $this->createBody($contract),
         ]);
         $statusCode = $response->getStatusCode(false);
         $responseContent = $response->getContent(false);
/*
         $statusCode = 200;
         $responseContent = <<<XML
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
               <soapenv:Header/>
               <S:Body xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
                  <ns2:altaPeticionPublicacionResponse xmlns:ns2="http://com.ejie.ac70a.webservice">
                     <return><![CDATA[<?xml version="1.0" encoding="ISO-8859-1"?>
            <peticion_publicacion_resultado xmlns="com/ejie/ac70a/integracionkontratazioa">
               <id_peticion_perfil>406927</id_peticion_perfil>
               <codigo_error>00000</codigo_error>
            </peticion_publicacion_resultado>]]></return>
                  </ns2:altaPeticionPublicacionResponse>
               </S:Body>
            </soapenv:Envelope>
         XML;

         $responseContent = <<<XML
         <?xml version="1.0" encoding="UTF-8"?>
         <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"><soapenv:Header/><S:Body xmlns:S="http://schemas.xmlsoap.org/soap/envelope/"><ns2:altaPeticionPublicacionResponse xmlns:ns2="http://com.ejie.ac70a.webservice"><return><![CDATA[<?xml version="1.0" encoding="ISO-8859-1"?>
         <peticion_publicacion_resultado xmlns="com/ejie/ac70a/integracionkontratazioa">
            <codigo_error>00102</codigo_error>
            <descripcion_error>
               <mensaje_principal>Error al realizar la validacion del xml: error de negocio</mensaje_principal>
               <lista_errores>
                  <error>El Expediente ya está utilizado en otro expediente con el mismo Poder adjudicador y la misma Entidad impulsora </error>
               </lista_errores>
            </descripcion_error>
         </peticion_publicacion_resultado>
         ]]></return></ns2:altaPeticionPublicacionResponse></S:Body></soapenv:Envelope>
XML;
*/
         if ($statusCode === Response::HTTP_OK) {
            $result = $this->proccessResponse($responseContent);
            return $result;
         } else {
            return $result[] = $responseContent;    
         }
      } catch (HttpExceptionInterface $e) {
         return $result[] = $e->getMessage();
      }
  }

  /**
   * Create the body of the Web Service Request
   * 
   * @param Contract $contract
   * 
   * @return string
   */
  private function createBody(Contract $contract) {
      $soapParams = [
         'date' => new \DateTime(),
         'origin' => $this->origin,
         'adjudicator' => $this->adjudicator,
         'entity' => $this->entity,
         'organ' => $this->organ,
         'powerType' => $this->powerType,
         'mainActivity' => $this->mainActivity,
         'soapUser' => $this->soapUser,
         'wsseUser' => $this->wsseUser,
         'wssePassword' => $this->wssePassword,
      ];
      $body = $this->twig->render('contract/_newContract.xml.twig', [
         'contract' => $contract,
         'params' => $soapParams,
      ]); 
      return $body;
   }

   /**
    * Proccesses WEBService Responses and parses result details.
    * 
    * @param String $response; 
    * 
    * @return array $result
    */
   private function proccessResponse($responseContent) {
      $response = [];
      $crawler = new Crawler($responseContent);
      $crawler = new Crawler($crawler->filterXPath('.//return')->innerText());
      $codigoError = $crawler->filterXPath('.//codigo_error')->count() > 0 ? $crawler->filterXPath('.//codigo_error')->text() : null;
      $mensajeError = $crawler->filterXPath('.//descripcion_error')->count() > 0 ? $crawler->filterXPath('.//descripcion_error')->text() : null;
      if ($codigoError === self::RESPONSE_OK) {
         $response['result'] = 'OK';
         $responseId = $crawler->filterXPath('.//id_peticion_perfil')->count() > 0 ? $crawler->filterXPath('.//id_peticion_perfil')->text() : null;
         $response['id'] = $responseId;
      } else {
         $response['result'] = 'NOK';
         $response['error'] = $mensajeError;
      }
      return $response;
   }

}