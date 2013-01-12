<?php

try {
  /**
   * Ein Script was den Request aus den Globals Inferred und serialisiert ausgibt
   */
  $request = \Psc\Net\HTTP\Request::infer();

  print serialize($request);
} catch (\Exception $e) {

  $message = $e->getMessage().' '.$e->getFile().':'.$e->getLine();
  $message = str_replace(array("\n","\"r"), ' ',$message);
  
  $response = \Psc\Net\HTTP\Response::create(
      500,
      (string) $e,
      array(
        'X-Psc-CMS-Error'=>'true',
        'X-Psc-CMS-Error-Message'=>$message
      )
  );
  
  $response->output();
}
?>