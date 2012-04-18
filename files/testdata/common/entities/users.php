<?php

namespace Entities;

$users = array();
$users['u1'] = new User('p.scheit@ps-webforge.com');
$users['u1']->hashPassword('u1password');

$users['u2'] = new User('i.karbach@ps-webforge.com');
$users['u2']->hashPassword('u2password');

$users['u3'] = new User('sensio@tourneyscript.net');
$users['u3']->hashPassword('u3password');
?>