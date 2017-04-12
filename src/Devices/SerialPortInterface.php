<?php  
namespace  Kamaro\Sdc\Devices;

interface SerialPortInterface{

  /**
   * Open Serial Port
   * @return  bool
   */
  public static function open();

  /**
   * Close Serial Port
   * @return  null
   */
  public static function close();

  /**
   * Write to serial port
   * @param   $string 
   * @return  mixed
   */
  public static function write($string);

  /**
   * Read from from port
   * @return  
   */
  public static function read();
  
  /**
   * Check if serial port is open
   * @return  
   */
  public static function isopen();

  /**
   * Write bytes to the port
   * @param   $string 
   * @return  mixed
   */
  public static function writebyte($string);

  /**
   * Read bytes from bort
   * @return  
   */
  public static function readbyte();

  /**
   * Count Bytes written to port
   * @return  
   */
  public static function inputcount();

  /**
   * Refresh buffer
   * @return  
   */
  public static function flush();

  /**
   * Set RTS on Devise
   * @return  
   */
  public static function setRTS($RTS);

  /**
   * Set DTR
   * @return  
   */
  public static function setDTR($DTR);

  /**
   * Set Break on device
   * @return  
   */
  public static function setBreak();

  /**
   * List available ports
   * @return array 
   */
  public static function getPorts();
}