<?php

use Psc\Doctrine\Helper as DoctrineHelper;

require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'bootstrap.php';

$doctrine = $container->getModule('Doctrine');

print DoctrineHelper::updateSchema(DoctrineHelper::FORCE, "\n", $doctrine->getEntityManager('tests'));
print DoctrineHelper::updateSchema(DoctrineHelper::FORCE, "\n", $doctrine->getEntityManager('default'));
