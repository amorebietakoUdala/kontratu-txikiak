<?php

namespace App\Service;

use App\Entity\Contract;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Twig\Environment;

class ContractNotifierService
{
   final public const RESPONSE_OK = '00000';

    public function __construct(
      private readonly HttpClientInterface $client, 
      private readonly Environment $twig, 
      private readonly LoggerInterface $logger,
      private $url, 
      private $origin, 
      private $adjudicator, 
      private $entity, 
      private $organ, 
      private $powerType, 
      private $mainActivity, 
      private $wsseUser, 
      private $wssePassword)

    {
    }

   /**
    * Create the body of the Web Service Request
    *
    *
    * @return array<int,string>
    */
   public function notify(Contract $contract, User $user) {
      $result = [];
      if (null === $contract) {
         return $errors[] = "error.nullContract";
      } 
      try {
         $body = mb_convert_encoding(preg_replace('/\>\s+\</m', '><', $this->createBody($contract, $user)), 'ISO-8859-1', 'UTF-8');
         $this->logger->debug('Body:'.$body);
         $response = $this->client->request('POST',$this->url, [
             'headers' => [
                  'Accept-Encoding' => 'gzip,deflate',
                  'Content-Type' => 'text/xml;charset=ISO-8859-1',
                  'SOAPAction' => '""',
                  'Connection' => 'Keep-Alive',
             ],
             'body' => $body,
         ]);
         $statusCode = $response->getStatusCode(false);
         $responseContent = $response->getContent(false);
         if ($statusCode === Response::HTTP_OK) {
            $this->logger->debug($responseContent);
            $result = $this->proccessResponse($responseContent);
            return $result;
         } else {
            return $result[] = $responseContent;    
         }
      } catch (HttpExceptionInterface $e) {
         $this->logger->debug('Exception Catched'.$e->getMessage());
         return $result[] = $e->getMessage();
      }
  }

  /**
   * Create the body of the Web Service Request
   *
   *
   * @return string
   */
  private function createBody(Contract $contract, User $user) {
      $soapParams = [
         'date' => new \DateTime(),
         'origin' => $this->origin,
         'adjudicator' => $this->adjudicator,
         'entity' => $this->entity,
         'organ' => $this->organ,
         'powerType' => $this->powerType,
         'mainActivity' => $this->mainActivity,
         'soapUser' => $user->getIdNumber(),
         'wsseUser' => $this->wsseUser,
         'wssePassword' => $this->wssePassword,
         'pubDate' => (new \DateTime())->modify('+1 day'),
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
      $crawler = new Crawler( mb_convert_encoding($crawler->filterXPath('.//return')->innerText(),'ISO-8859-1','UTF-8') );
      $codigoError = $crawler->filterXPath('.//codigo_error')->count() > 0 ? $crawler->filterXPath('.//codigo_error')->text() : null;
      $mensajeError = $crawler->filterXPath('.//descripcion_error')->count() > 0 ? $crawler->filterXPath('.//descripcion_error')->text() : null;
      if ($codigoError === self::RESPONSE_OK) {
         $response['result'] = 'OK';
         $responseId = $crawler->filterXPath('.//id_peticion_perfil')->count() > 0 ? $crawler->filterXPath('.//id_peticion_perfil')->text() : null;
         $response['id'] = $responseId;
         $response['raw'] = $responseContent;
      } else {
         $response['result'] = 'NOK';
         $response['error'] = $mensajeError;
         $response['raw'] = $responseContent;
      }
      return $response;
   }

}