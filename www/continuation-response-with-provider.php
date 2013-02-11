<?php

use Psc\Net\HTTP\Response;

$response = Response::create(200, 'continuation(0)', array('Content-Type'=>'text/html; charset=utf-8'));

$hasOutput = function ($tick) {
  return ($tick % 2) == 0;
};

$getOutput = function($tick) {
  return sprintf("continuation(%d)<br />\n", floor($tick / 2));
};

$response->setOutputClosure(function () use ($hasOutput, $getOutput) {
  while (ob_get_level() > 0) {
    ob_end_flush();
  }
  
  // this will force internet explorer to not wait for the ouput when the tab / windows is NEW (reload always works as expected)
  print str_repeat(" ", 256);
  flush();
  
  // up it goes
  print "starting upload<br />\n";
  flush();
  
  $ms = 400;
  for($i=1; $i<=20; $i++) {
    usleep($ms *  1000);
    if ($hasOutput($i)) {
      print $getOutput($i);
    }
    
    flush();
  }
});

$response->output();
?>