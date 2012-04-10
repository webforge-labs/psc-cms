<?php

$request = \Psc\Net\HTTP\Request::infer();
$arrayBody = (array) $request->getBody();
$input = new \Psc\DataInput($arrayBody);


$defaultHeaders = array(
  'Content-Type'=>'text/html; charset=UTF-8'
);

$content = $input->get('content','','');
$headers = $input->get('headers',$defaultHeaders,$defaultHeaders);
if (($code = (int) $input->get('code',0,0)) == 0) {
  $code = 200;
}

\Psc\Net\HTTP\Response::create($code, $content, $headers)->output();
?>