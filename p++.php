<?php
echo "PHP serial extension version ".ser_version();
echo "<br>\r\n";

$functions = get_extension_funcs("win_serial");
echo "Functions available in the $module extension:<br>\n";
foreach($functions as $func) {
    echo $func."<br>";
}
echo "<br>";

if (ser_isopen() == true )
    echo "Port is open<br>\r\n";
else
    echo "Port is closed<br>\r\n";

echo "Opening the port ...\r\n";
echo "<br>\r\n";
echo "Result = ";
echo ser_open("COM1", 9600, 8, "None", "1", "None");
echo "<br>\r\n";

if (ser_isopen() == true )
    echo "Port is open<br>\r\n";
else
    echo "Port is closed<br>\r\n";

echo "Writing (string) AT\\r ...\r\n";
echo "<br>\r\n";
echo "Result = ";
echo ser_write("AT\r");
echo "<br>\r\n";

echo "Sleeping ...\r\n";
echo "<br>\r\n";

sleep(1);

echo "Bytes available for reading: ".ser_inputcount()."<br>\r\n";
/* Flush test
ser_flush(true, true);
echo "Bytes available for reading: ".ser_inputcount()."<br>\r\n";
*/

echo "Reading (string) ...\r\n";
echo "<br>\r\n";
echo "Result = ";
echo ser_read();
echo "<br>\r\n";

echo "Bytes available for reading: ".ser_inputcount()."<br>\r\n";

// Once again, with byte operations

echo "Writing (byte) AT\\r ...\r\n";
echo "<br>\r\n";
ser_writebyte(0x41); ser_writebyte(0x54); ser_writebyte(0xD);

sleep(1);

echo "Bytes available for reading: ".ser_inputcount()."<br>\r\n";

echo "Reading (byte) ...\r\n";
for ($i=0; $i<10; $i++)
{
    $j = ser_readbyte(); 
    echo sprintf("%c", $j);
}
echo "<br>\r\n";

echo "Bytes available for reading: ".ser_inputcount()."<br>\r\n";

// Test RTS, DTR signals

ser_setRTS(true);
ser_setDTR(true);
sleep(1);
ser_setRTS(false);
ser_setDTR(false);


echo "Closing the port ...\r\n";
echo "<br>\r\n";
echo "Result = ";
echo ser_close();
echo "<br>\r\n";

if (ser_isopen() == true )
    echo "Port is open<br>\r\n";
else
    echo "Port is closed<br>\r\n";

?>