<?php  
namespace  Kamaro\Sdc\Devices;

interface SerialPortInterface{

  /**
   * Open Serial Port
   * @return  bool
   */
  public function open();

  /**
   * Close Serial Port
   * @return  null
   */
  public function close();

  /**
   * Write to serial port
   * @param   $string 
   * @return  mixed
   */
  public function write($string);

  /**
   * Read from from port
   * @return  
   */
  public function read();
  
  /**
   * Check if serial port is open
   * @return  
   */
  public function isopen();

  /**
   * Write bytes to the port
   * @param   $string 
   * @return  mixed
   */
  public function writeByte($string);

  /**
   * Read bytes from bort
   * @return  
   */
  public function readByte();

  /**
   * Count Bytes written to port
   * @return  
   */
  public function inputCount();

  /**
   * Refresh buffer
   * @return  
   */
  public function flush();

  /**
   * Set RTS on Devise
   * @return  
   */
  public function setRTS($RTS);

  /**
   * Set DTR
   * @return  
   */
  public function setDTR($DTR);

  /**
   * Set Break on device
   * @return  
   */
  public function setBreak();

  /**
   * List available ports
   * @return array 
   */
  public static function getPorts();
}