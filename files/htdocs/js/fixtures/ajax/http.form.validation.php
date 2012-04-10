<?php

// @TODO body
\Psc\Net\HTTP\Response::create(400, 'Dies ist ein 400 Validation Fehler', array('X-Psc-Cms-Validation'=>'true'))->output();
?>