<?php require 'vendor/autoload.php'; ?>
<html>
<head>
<title>PHP serial extension test page</title>
</head>
<body>
<h3>PHP SDC test page</h3>
<?php 
  $device  = new Kamaro\Sdc\SDCController('COM7');
  echo "<h2>AVAILABLE PORTS</h2>";
  foreach (getPorts() as $key => $value) {
      echo $value .'<br/>';
    }  
  echo "<h3> SDC ID </h3>".$device->getID();

  echo "<h3> SEND INFORMATION OF TAXES </h3>";
  $s = "NS01012345,100600570,11/05/2016 12:35:20,100,0.00,18.00,0.00,0.00,11.00,12.00,0.00,0.00,0.00,1.83,0.00,0.00";
  
  echo $device->sendReceiptData($s);
  
  echo "<h3> REQUEST SIGNATURE </h3>";

  foreach ($device->getSignature(100) as $key => $value) {
    echo $key.' : '.$value.'<br/>';
  }
  
  echo "<h3> SDC STATUS </h3>";
  foreach ($device->getStatus() as $key => $value) {
    echo $key.' : '.$value.'<br/>';
  }

?>