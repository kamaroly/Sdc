<html>
<head>
<title>PHP serial extension test page</title>
</head>
<body>
<h3>PHP SDC test page</h3>
<?php 

  function build_table($array){
    // start table
    $html = '<table width="40%" border="1">';
    // header row

    foreach($array as $key=>$value){
          $html .= '<tr>';
            $html .= '<th>' . htmlspecialchars($key) . '</th>';
            $html .= '<td>' . htmlspecialchars($value) . '</td>';
          $html .= '</tr>';
        }
    // finish table and return it

    $html .= '</table>';
    return $html;
}

/**
 * [get_string_between description]
 * @param  [type] $string [description]
 * @param  [type] $start  [description]
 * @param  [type] $end    [description]
 * @return [type]         [description]
 */
function _get_string_between($string, $start, $end){
    $string = " ".$string;
    $ini = strpos($string,$start);
    if ($ini == 0) return "";
    $ini += strlen($start);
    $len = strpos($string,$end,$ini) - $ini;
    return substr($string,$ini,$len);
}

/**
 * @author Kamaro Lambert
 * @method to convert strto hex
 */
function strToHex($string)
{

  //Force to Convert to string  
  $string=strval($string);
  $hex='';
  for ($i=0; $i < strlen($string); $i++)
  {
  $hex .= sprintf("%02x",ord($string[$i]));
  }
  return str_split(strtoupper($hex),2);
}

  /**
   * @Author Kamaro Lambert
   * @name hexToStr()
   * @example Base_convert_lib->hexToStr()
   * @param  string $hex
   * Method to convert  Hexadecimal to string
   */
function hexToStr($hex){
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}


function hexdecs($hex)
{
    // ignore non hex characters
    $hex = preg_replace('/[^0-9A-Fa-f]/', '', $hex);
    
    // converted decimal value:
    $dec = hexdec($hex);
    
    // maximum decimal value based on length of hex + 1:
    // number of bits in hex number is 8 bits for each 2 hex -> max = 2^n
    // use 'pow(2,n)' since '1 << n' is only for integers and therefore limited to integer size.
    $max = pow(2, 4 * (strlen($hex) + (strlen($hex) % 2)));
    
    // complement = maximum - converted hex:
    $_dec = $max - $dec;
    
    // if dec value is larger than its complement we have a negative value (first bit is set)
    return $dec > $_dec ? -$_dec : $dec;
}


// echo "UID=".ser_getuid();
 /**
     * @author Kamaro Lambert
     * @method to open a port
     */
  function _open_port($port = 'COM7')
  {
    
    if(substr($port, 3)>9)
    {
      $port="\\\\.\\$port";
    }

    ser_register("KAMARO LAMBERT #1","-546625902");

     //Opening Port...
    ser_open($port, 9600, 8, "None", 1, "None");
     
    // Clears input and/or output buffers.
    ser_flush(true,true);
 
   //Check if the port is open 
   return ser_isopen();
  }
 /**
   * @method to get the get_sdc_status
   * @return [type] [description]
   */
  function get_sdc_status()
  {
     ser_close();
     _open_port();
     error_reporting(E_ERROR);
    $string_dig= '01 24 20 E7 05 30 31 33 30 03';
   
      $string_array_dig=explode(' ',$string_dig);
       $bytes_dig=" ";        
       foreach ($string_array_dig as $string_hex_dig=>$value_dig)
       {
           ser_writebyte(" ".hexdecs($value_dig)."\r\n");
       }
       sleep(1);
     //Send request to the SDC asking the response 
    $str = ser_read();

      $returned_data=implode(" ", strToHex($str));
      
      $returned_data=_get_string_between($returned_data,"E7","04");
      
      $hex_string=str_replace(" ", "", $returned_data);
      $string=explode(",", hexToStr($hex_string));
      $data['SDC serial number']                     =$string[0];
      $data['Firmware version']                      =$string[1];
      $data['Hardware revision']                     =$string[2];
      $data['The number of current SDC daily report']=$string[3];   
      $data['Last remote audit date and time']       =$string[4];
      $data['Last local audit date and time']        =$string[5];     

      return $data;   
  }

    /**
   * method to get the sdc_id
   * @return [type] [description]
   */
  function get_sdc_id()
  {
      ser_close();
      _open_port();
  	  error_reporting(E_ERROR);
      $string_dig="01 24 20 E5 05 30 31 32 3E 03";
   
      $string_array_dig=explode(' ',$string_dig);
      
       $bytes_dig=" ";        
       foreach ($string_array_dig as $string_hex_dig=>$value_dig)
       {
         ser_writebyte(" ".hexdecs($value_dig)."\r\n");
       }

     sleep(1);
     
     //Send request to the SDC asking the response 
     $string = ser_read();
     ser_close();
     return substr($string, strpos($string,'SDC'), 12);    
  }

  echo "<strong>SDC ID:</strong>".get_sdc_id();
  echo "<h3>DETAILS:</h3>";
  echo build_table(get_sdc_status());
  echo "<br/>";
  ser_close();
  echo "<br/>";
/**
 * function to return the available com port
 * @return arrays comm_list
 */
function _avalable_comport_list() 
{ 
        $comm = shell_exec('mode'); 
        if(substr_count($comm,'COM')<1) { 
            $comm_list[0] = 'None'; 
        } else { 
            $conn = explode(' ',$comm); 
            $count = count($conn); 
            for($i=0;$i<$count;$i++) { 
                if(substr_count($conn[$i],'COM')<1) { 
                    $comm_list[$i] = ''; 
                } else { 
                    $comm_list[$i] = str_replace(':','',substr($conn[$i],0,5)).'-'; 
                } 
            } 
        } 
        $comm = implode('',$comm_list); 
        $comm = trim($comm); 
        $comm = trim(str_replace('-',' ',$comm)); 
        $comm_list = explode(' ',$comm); 
      return $comm_list ; 
    } 

$ports = _avalable_comport_list();

echo "<strong>Ports</strong> <br/>";
foreach ($ports as $key => $value) {
	echo "$value <br/>";
}


// echo "UID=".ser_getuid();
echo "<br/>";
$module = 'win_serial';
 
if (extension_loaded($module)) {
  $str = "<strong>Module loaded</strong>";
} else {
 $str = "Module $module is not compiled into PHP";
 die("Module $module is not compiled into PHP");
}
 echo "$str<br>";
 
$functions = get_extension_funcs($module);
echo "Functions available in the $module extension:<br>\n";
foreach($functions as $func) {
    echo $func."<br>";
}
echo "<br>";

echo "Version ".ser_version();
echo "<br>";

echo "<br>";

?>
