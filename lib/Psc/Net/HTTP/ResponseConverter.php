<?php

namespace Psc\Net\HTTP;

use Psc\Net\ServiceResponse;
use Psc\Net\Service;
use Psc\Code\NotImplementedException;
use Psc\Code\Code;

/**
 * @TODO ganz dringend accept parsen lernen
 *
 * Wir haben hier die Chance vielleicht einen Performance-Boost für die JavaScript sachen zu erhalten: (vorher testen obs das bringt)
 *
 * da wir hier ja erst das Objekt in HTML + JavaScript umwandeln, könnten wir mit dem AST-Runner (den es später mal geben wird für html + js)
 * hier alles javascript des Requests sammeln und quasi getrennt vom HTML übermitteln (oder per Header, oder wie auch immer)
 *
 * dann hätten wir eine Chance alles in einem JavaScript-Block zu machen und das wirklich erst, nachdem das HTML da ist
 * quasi ein html + code paket an den client zurückschicken
 * wir würden den Javascript "pushen" statt ihn aus dem HTML zu "pullen"
 */
class ResponseConverter extends \Psc\SimpleObject {
  
  protected $prettyPrintJSON = TRUE;
  
  protected $headers;
  
  public function __construct() {
    // eventManager
  }
  
  /**
   * Wandelt eine ServiceResponse in eine HTTP-Response um
   * 
   * wandelt auch den komplexen Inhalt der ServiceResponse um. D. h. unsere HTTP-Response ist dann eine "dumme" Response
   * @return Psc\Net\HTTP\Response
   */
  public function fromService(ServiceResponse $response, Request $request) {
    if ($response->getStatus() === Service::OK) {
      list($format,$contentType) = $this->decideFormat($response, $request);
      
      $this->headers = array('Content-Type'=>$contentType.'; charset=utf-8');
      
      // Response Meta
      if (($meta = $response->getMetadata()) != NULL) {
        $this->headers = array_merge($this->headers, $meta->toHeaders());
      }
      
      return $this->convertResponse($response, $format, $contentType, $request);
      
    } elseif ($response->getStatus() === Service::ERROR) {
      throw new NotImplementedException('Was soll das hier aussagen? Warum keine HTTPException?');

    } else {
      throw new NotImplementedException('Der Status "%s" ist nicht bekannt.',$response->getStatus());
    }
  }

  protected function convertResponse(ServiceResponse $response, $format, $mimeContentType, $request) {
    // @TODO Event Triggern?
    $body = $response->getBody();
    
    $isJsonRequest = mb_strpos($request->getHeader()->getField('Accept'), 'application/json') === 0;

    /* Sonderbehandlung JSON uploads */
    if ($format === ServiceResponse::JSON_UPLOAD_RESPONSE) {
      if (!$isJsonRequest) {
        // firefox und alle xhr browser bekommen den richtigen ContentType
        
        // ie und so bekommen plain:
        $this->headers['Content-Type'] = 'text/plain';
        $this->headers['Content-Disposition'] = 'inline; filename="files.json"';
        $this->headers['X-Content-Type-Options'] = 'nosniff';
        $this->headers['Vary'] = 'Accept';
      }
      
      $format = ServiceResponse::JSON; // weiter
    }
    
    /* JSON */
    if ($format === ServiceResponse::JSON) {
      if (($json = $this->createJSONResponse($body)) != NULL) {
        return $json;
      }

    /* ICAL */
    } elseif ($format === ServiceResponse::ICAL) {
      return $this->createResponse($body);
    
    /* HTML */
    } elseif ($format === ServiceResponse::HTML) {
      if (is_string($body)) {
        return $this->createResponse($body);
      } elseif ($body instanceof \Psc\HTML\HTMLInterface) {
        return $this->createResponse($body->html());
      }
    
    /* XLSX */
    } elseif ($format === ServiceResponse::XLSX) {
      $excel = $body;
      
      $output = function () use ($excel) {
        $writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
      };
      
      $this->headers['Content-Type'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
      $this->headers['Content-Disposition'] = 'attachment;filename="'.$excel->getProperties()->getCustomPropertyValue('filename').'.xlsx"';
      $this->headers['Cache-Control'] = 'max-age=0';
      
      $response = Response::create(200, NULL, $this->headers);
      $response->setOutputClosure($output);
      
      return $response;
    } elseif ($format === ServiceResponse::SOME_FILE) {
      if ($body instanceof \Psc\CMS\UploadedFile) {
        $uplFile = $body;
        
        if ($isJsonRequest) {
          return $this->createJSONResponse($uplFile);
        } else {
          // sende einen application download krams
          
          $this->headers['Content-Type'] = 'application/octet-stream';
          $this->headers['Content-Disposition'] = 'attachment;filename="'.$uplFile->getDownloadFilename().'"';
          $this->headers['Content-Transfer-Encoding'] = 'binary';
          $this->headers['Pragma'] = 'public';
          $this->headers['Content-Length'] = $uplFile->getFile()->getSize();

          $output = function () use ($uplFile) {
            readfile((string) $uplFile->getFile());
          };
          
          $response = Response::create(200, NULL, $this->headers);
          $response->setOutputClosure($output);
          return $response;
        }
      }
    }
    
    throw new ResponseConverterException(
      sprintf(
        "Der Inhalt der Service-Response konnte nicht in eine HTTP-Response umgewandelt werden. Der Body der ServiceResponse ist: %s. Format wurde ermittelt als: '%s' ('%s').",
        Code::varInfo($body),
        $format,
        $mimeContentType
      )
    );
  }

  protected function createResponse($body) {
    return Response::create(
      200,
      $body,
      $this->headers
    );
  }
  
  
  protected function createJSONResponse($body) {
    if ($body instanceof \Psc\JS\JSON) {
      return $this->createResponse($body->JSON());
    
    } elseif (is_array($body) || (is_object($body) && $body instanceof \stdClass)) {
      return $this->createResponse($this->prettyPrintJSON ? \Psc\JS\Helper::reformatJSON(json_encode($body)) : json_encode($body));
    
    } elseif ($body instanceof \Psc\Data\Exportable) {
      $body = $body->export();
      return $this->createResponse($this->prettyPrintJSON ? \Psc\JS\Helper::reformatJSON(json_encode($body)) : json_encode($body));
    }
  }
    
  
  /**
   * @return list($format, $mimeContentType)
   */
  public function decideFormat(ServiceResponse $response, Request $request) {
    if ($response->getFormat() !== NULL)
      $format = $response->getFormat();
    elseif ($request->accepts('application/json'))
      $format = ServiceResponse::JSON;
    else
      $format = ServiceResponse::HTML;
      
    return array($format, $this->getContentType($format));
  }
  
  protected function getContentType($format) {
    if ($format === ServiceResponse::JSON) {
      $contentType = Response::CONTENT_TYPE_JSON;
    } elseif($format === ServiceResponse::HTML) {
      $contentType = Response::CONTENT_TYPE_HTML;
    } elseif($format === ServiceResponse::ICAL) {
      $contentType = Response::CONTENT_TYPE_ICAL;
    } else { // fallback
      $contentType = Response::CONTENT_TYPE_HTML;
    }
    
    return $contentType;
  }
}
?>