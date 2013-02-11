<?php

use Psc\Net\HTTP\Response;

$response = Response::create(200, 'continuation(0)', array('Content-Type'=>'text/html; charset=utf-8'));

$o = new \Psc\Net\PHPResponseOutput;
$response->setOutputClosure(function () use ($o) {
  $o->start();
  
  // up it goes
  $o->write("starting upload<br />\n");
  $o->flush();
  
  $ms = 400;
  for($i=1; $i<=10; $i++) {
    usleep($ms *  1000);
    $o->write(sprintf("continuation(%d)<br />\n", $i));
    $o->flush();
  }
});
$response->output();

?>