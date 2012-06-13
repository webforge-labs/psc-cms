<?php

require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'bootstrap.php';

$em = \Psc\PSC::getProject()->getModule('Doctrine')->getEntityManager('tests');

print \Psc\Doctrine\Helper::updateSchema(\Psc\Doctrine\Helper::FORCE, "\n", $em);

?>