<?php

use Psc\PSC;
use Psc\Doctrine\Helper as DoctrineHelper;

require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'bootstrap.php';

$doctrine = PSC::getProject()->getModule('Doctrine');

print DoctrineHelper::updateSchema(DoctrineHelper::FORCE, "\n", $doctrine->getEntityManager('tests'));
print DoctrineHelper::updateSchema(DoctrineHelper::FORCE, "\n", $doctrine->getEntityManager('default'));
?>