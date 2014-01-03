<?php
  $files = scandir(dirname(__FILE__), FALSE);
  echo "Datum\tPiratenLSA\n";
  
  foreach ($files as $file) {
     if (preg_match('#mitglieder-(.*?)\.csv#', $file, $m)) {
       $lines = explode("\n", file_get_contents($file));
       $fd = $m[1];
       $fc = 0;
       foreach ($lines as $line) {
          if (preg_match('#.*?;(\d+)#',$line,$m)) {
             $fc += intval($m[1]);
          }
       }
       echo "$fd\t$fc\n";
     }
  }
?>