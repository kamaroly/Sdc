<?php

//-- settings --//

//brainboxes serial ports
//on 'nix start with cu.usbserial-
//on windows starts with com : must be lower case in windows and end with a colon
// $portName = 'com8:';
$portName = 'COM7:';
//$baudRate = 115200;
$baudRate = 115200;
$bits = 8;
$spotBit = 1;

header( 'Content-type: text/plain; charset=utf-8' ); 
?>
Serial Port Test
================
<?php


function echoFlush($string)
{
	echo $string . "\n";
	flush();
	ob_flush();
}

if(!extension_loaded('dio'))
{
	echoFlush( "PHP Direct IO does not appear to be installed for more info see: http://www.php.net/manual/en/book.dio.php" );
	exit;
}

try 
{
	//the serial port resource
	$bbSerialPort;
	
	echoFlush(  "Connecting to serial port: {$portName}" );
	
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') 
	{ 
		$bbSerialPort = dio_open($portName, O_RDWR );
		//we're on windows configure com port from command line
		exec("mode {$portName} baud={$baudRate} data={$bits} stop={$spotBit} parity=n xon=on");
	} 
	else //'nix
	{
		$bbSerialPort = dio_open($portName, O_RDWR | O_NOCTTY | O_NONBLOCK );
		dio_fcntl($bbSerialPort, F_SETFL, O_SYNC);
		//we're on 'nix configure com from php direct io function
		dio_tcsetattr($bbSerialPort, array(
			'baud' => $baudRate,
			'bits' => $bits,
			'stop'  => $spotBit,
			'parity' => 0
		));
	}
	
	if(!$bbSerialPort)
	{
		echoFlush( "Could not open Serial port {$portName} ");
		exit;
	}
	else
	{
	echoFlush( "Serial port open {$portName} ");
		//exit;	
		
	}
	// send data

	//////////////////
//$dataToSend=array("\x01\x24\x20\xE5\x05\x30\x31\x33\x30\x03");


//$dataToSend=array("\x01\x90\x22\xC6\x4E\x53\x54\x45\x53\x30\x31\x30\x31\x32\x33\x34\x35\x2C\x31\x30\x30\x36\x30\x30\x35\x37\x30\x2C\x31\x37\x2F\x30\x37\x2F\x32\x30\x31\x33\x20\x30\x39\x3A\x32\x39\x3A\x33\x37\x2C\x31\x2C\x30\x2E\x30\x30\x2C\x31\x38\x2E\x30\x30\x2C\x30\x2E\x30\x30\x2C\x30\x2E\x30\x30\x2C\x31\x31\x2E\x30\x30\x2C\x31\x32\x2E\x30\x30\x2C\x30\x2E\x30\x30\x2C\x30\x2E\x30\x30\x2C\x30\x2E\x30\x30\x2C\x31\x2E\x38\x33\x2C\x30\x2E\x30\x30\x2C\x30\x2E\x30\x30\x05\x31\x36\x37\x39\x03"); //RECEIPT DATA
//$dataToSend=array("\x01\x25\x23\xC8\x01\x05\x30\x31\x31\x36\x03"); //dat

$dataToSend=array("\x01\x24\x20\xE5\x05\x30\x31\x32\x3E\x03"); //SDC ID Request
//$dataToSend=array("\x01\x24\x21\xE5\x05\x30\x31\x32\x3F\x03"); //SDC ID Request


//$dataToSend=array("\x01\x24\x20\xE7\x05\x30\x31\x33\x30\x03"); //SDC ID Request
//$dataToSend=array("\x01\x24\x20\xE7\x05\x30\x31\x33\x30\x03"); //SDC STATUS
//$dataToSend=array("\x01\x26\x22\xC7\x52\x31\x05\x30\x31\x39\x37\x03"); //SIGNATURE
print_r($dataToSend);
foreach ($dataToSend as $message) {
   // echo $message;
	$bytesSent = dio_write($bbSerialPort,$message);
}

	echoFlush( "Writing to serial port data: \"{$message}\"" );
	//$bytesSent = dio_write($bbSerialPort, $dataToSend);
	echoFlush( "Sent: {$bytesSent} bytes" );


	
	//date_default_timezone_set ("Europe/London");
	
	$runForSeconds = new DateInterval("PT10S"); //10 seconds
	//$endTime = (new DateTime())->add($runForSeconds);
	$endTime1 = new DateTime();
	$endTime = $endTime1->add($runForSeconds);
	
	echoFlush(  "Waiting for {$runForSeconds->format('%S')} seconds to recieve data on serial port" );
	$time_start = time();
	$TIMEOUT = 10;
	$resultat='';
	while($time_start + $TIMEOUT > time()) 
	{
		$resultat.= dio_read( $bbSerialPort);
		usleep(100000);
	}
	echoFlush($resultat);
	//$data = dio_read($bbSerialPort, 256); //this is a blocking call
	/*while (new DateTime() < $endTime) {
	
		$data = dio_read($bbSerialPort, 256); //this is a blocking call
		//$data = dio_read($bbSerialPort);
		
	if ($data) {
			echoFlush(  "Data Recieved: ".$data );

		}
	}*/
	
	echoFlush(  "Closing Port" );
	
	dio_close($bbSerialPort);

} 
catch (Exception $e) 
{
	echoFlush(  $e->getMessage() );
	exit(1);
} 

?>