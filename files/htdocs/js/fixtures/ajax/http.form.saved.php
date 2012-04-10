<?php

$json = '{
  "id":2,
  "birthday":{
    "date":"5698800",
    "timezone":"Europe\/Berlin"
  },
  "yearKnown":false,
  "name":"other",
  "firstName":"Someone",
  "created":{
    "date":"1331742644",
    "timezone":"Europe\/Berlin"
  }
}';

\Psc\Net\HTTP\Response::create(200, $json, array('Content-Type'=>'application/json'))->output();
?>
