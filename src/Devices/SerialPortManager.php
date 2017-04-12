<?php  
namespace  Kamaro\Sdc\Devices;

class SerialPortManager implements SerialPortInterface{
 
  /**
   * Open Serial Port
   * @return  bool
   */
  public static function open($port = "COM5",$licenseOwner = "KAMARO LAMBERT #1",$licenseKey = "-546625902"){

    // Fix a windows bug where by if port has two digit
    // It has to be prefixed by 4 slahes \\\\\
    if(substr($port, 3)>9){
      $port="\\\\.\\$port";
    }
    ser_register($licenseOwner,$licenseKey);
    //Opening Port...
    ser_open($port, 9600, 8, "None", 1, "None");
    // Clears input and/or output buffers.
    ser_flush(true,true);
   //Check if the port is open 
   return ser_isopen();
  }

  /**
   * Close Serial Port
   * @return  null
   */
  public static function close(){
  	return  ser_close();
  }

  /**
   * Write to serial port
   * @return  
   */
  public static function write($string){
  	return ser_write($string);
  }

  /**
   * Read from from port
   * @return  
   */
  public static function read(){
  	return ser_read();
  }
  
  /**
   * Check if serial port is open
   * @return  
   */
  public static function isopen(){
  	return ser_isopen();
  }

  /**
   * Write bytes to the port
   * @return 
   */
  public static function writebyte($byte){
  	return ser_writebyte($byte);
  }

  /**
   * Read bytes from bort
   * @return  
   */
  public static function readbyte(){
  	return ser_readbyte();
  }

  /**
   * Count Bytes written to port
   * @return  
   */
  public static function inputcount(){
  	return ser_inputcount();
  }

  /**
   * Refresh buffer
   * @return  
   */
  public static function flush(){
  	return ser_flush(true,true);
  }

  /**
   * Set RTS on Devise
   * @return  
   */
  public static function setRTS($rts){
  	return ser_setRTS($rts);
  }

  /**
   * Set DTR
   * @return  
   */
  public static function setDTR($dtr){
  	return ser_setDTR($dtr);
  }

  /**
   * Set Break on device
   * @return  
   */
  public static function setBreak(){
  	return ser_setBreak();
  }

  /**
   * Get available ports
   * @return  array | mixed
   */
  public static function getPorts(){
	 $comm         = shell_exec('mode'); 
	 $comm_list[0] = 'None'; 
     
    if(substr_count($comm,'COM') < 1) { 
       return  $comm_list;
    } 

    $conn = explode(' ',$comm); 
    $count = count($conn); 

    for($i=0;$i<$count;$i++) { 

    	// If this port is not COM then go to next 
        if(substr_count($conn[$i],'COM')<1) { 
            $comm_list[$i] = ''; 
            continue;
        } 

        $comm_list[$i] = str_replace(':','',substr($conn[$i],0,5)).'-'; 
    } 

    // Clean Data and response
    $comm = implode('',$comm_list); 
    $comm = trim($comm); 
    $comm = trim(str_replace('-',' ',$comm)); 
    $comm_list = explode(' ',$comm); 
    return $comm_list; 
  }
}