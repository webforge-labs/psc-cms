<?php

namespace Psc\Code;

class MyInjectedObject {
}

$it = 10000;

if (isset($_GET['isset'])) {
  $container = new IssetDPIContainer();
} else {
  $container = new ClosureDPIContainer();
}

for ($i=1; $i<=$it; $i++) {
  $object = $container->getObject();
}
?>