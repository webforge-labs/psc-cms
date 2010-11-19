<?php

var_dump(array('$ 25','key'=>'value'));
exit;


$usedWidth = 5;
$cWidth = 16;

$nullWidths = array(2,3,1);

if ($usedWidth < $cWidth && is_array($nullWidths)) {
  $grids = count($nullWidths);
  $avaibleWidth = $cWidth - $usedWidth;
      
  if ($avaibleWidth % $grids == 0)
    $newWidths = array_combine($nullWidths,array_fill(0,$grids,(int) $avaibleWidth/$grids));
  else {
    $avgWidth = (int) ceil($avaibleWidth/$grids);
    $newWidths = array_combine($nullWidths,array_merge(array_fill(0,$grids-1,$avgWidth),array($avaibleWidth - $avgWidth * ($grids-1))));
  }
}

var_dump($newWidths);

?>