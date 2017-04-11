<?php
$module = 'win_serial';
 
if (extension_loaded($module)) {
} else {
 die("Module $module is not compiled into PHP");
}
 
$functions = get_extension_funcs($module);
echo "Functions available in the $module extension:<br>\n";
foreach($functions as $func) {
    echo $func."<br>";
}

echo "<p>";
$str = ser_version();
echo "Version: $str";

echo "<p>";
echo "Open port";
ser_open("COM1", 9600, 8, "None", 1, "None");

echo "<p>";
if (ser_isopen() == true )
    echo "Port is open<br>\r\n";
else
    echo "Port is closed<br>\r\n";

echo "<p>";
echo "Setting DTR<br>";
ser_setDTR(False);

sleep(1);

echo "<p>";
echo "Setting text mode";
ser_write("AT+CMGF=1\r\n");

echo "<p>";
echo "Waiting";
sleep(1);

echo "<p>Reading answer";

$str = ser_read();
echo $str;

echo "<p>";
echo "List SMS";
ser_write("AT+CMGL=\"ALL\"\r\n");

echo "<p>";
echo "Waiting";
sleep(5);

echo "<p><pre>";
$str = ser_read();
echo $str;

echo "<p>";
echo "Close port";
ser_close();
?>
