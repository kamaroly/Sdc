<?php
namespace Kamaro\Sdc;

use  Kamaro\Sdc\Devices\SerialPortManager;

/**
 * KPOS
 *
 * An Point of Sale application 
 *
 * @package   KPOS
 * @author    Kamaro Team
 * @copyright Copyright (c) 2011 - 2014, Kamaro, Inc.
 * @license   http://codeigniter.com/user_guide/license.html
 * @link    http://kamaropos.com
 * @since   Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Common Functions
 *
 * Loads the base classes and executes the request.
 *
 * @package     KAMARO Point of Sale
 * @subpackage  KAMARO Point of Sale
 * @category    Common Functions fiscal Devices
 * @author      Kamaro Lambert (Thanks to Habamwabo Danny for the contribution)
 * @link        http://kamaropos.com/user_guide/
 */

// ------------------------------------------------------------------------

/**
* Determines if the SDC is connected then set it .
*
*
* @access public
* @param  string
* @return bool  TRUE if the current version is $version or higher
*/

Class SDCController { 
   /**
    * Contains Device
    * @var 
    */
   protected $device;

   function __construct(){

      error_reporting(E_ERROR);
      $this->device = new SerialPortManager();

     $this->device->open();
   }

   /**
    * Get SDC ID
    * @return string
    */
   public function getID(){
      // Hex for requesting signature
      $string_dig="01 24 20 E5 05 30 31 32 3E 03";
      
       // Turn this into arry so that we can be able
       // to write byte by byte
       $string_array_dig=explode(' ',$string_dig);
             
       foreach ($string_array_dig as $string_hex_dig=>$value_dig)
       {
         $this->device->writeByte(" ".hexToByte($value_dig)."\r\n");
       }

     sleep(1);
     //Send request to the SDC asking the response 
     $string = $this->device->read();

     $this->device->close();

     return substr($string, strpos($string,'SDC'), 12);  
   }

  /**
   * Get the Get SDC status
   * @return array
   */
  public function getStatus(){
       
       // Bytes for getting status
       $string_dig= '01 24 20 E7 05 30 31 33 30 03';
   
       $string_array_dig=explode(' ',$string_dig);
       
       foreach ($string_array_dig as $string_hex_dig=>$value_dig){
           $this->device->writeByte(" ".hexToByte($value_dig)."\r\n");
       }
      
      sleep(1);
      //Send request to the SDC asking the response 
      $str =  $this->device->read();
      
      $this->device->close();

      $returned_data = implode(" ", strToHex($str));
      
      $returned_data = getStringBetween($returned_data,"E7","04");
      
      $hex_string    = str_replace(" ", "", $returned_data);

      $string        = explode(",", hexToStr($hex_string));

      $data['SDC serial number']                     = $string[0];
      $data['Firmware version']                      = $string[1];
      $data['Hardware revision']                     = $string[2];
      $data['The number of current SDC daily report']= $string[3];   
      $data['Last remote audit date and time']       = $string[4];
      $data['Last local audit date and time']        = $string[5];     
      return $data;   
  }


    /**
     * @author Kamaro Lambert
     * @method to get the length of the command before passing it to sdc
     * */
    private function getLength(array $hex){
     //find the length by countind the data and adding 
     //leng itsself mseq,cmd,05=4, and 20h which is 32 in decimal
     $length= dechex(count($hex_data_array)+4+32);
     return strtoupper($length);
    }

  /**
   * @author Kamaro Lambert
   * Method to get the CheckSum for the hex bytes between 01 excluded and 05 included
   * @param array $hex_array
   * @return string
   */
  private function getCheckSum(array $hex_array){
     //Initiating the checksum variable
     $sdc_checksum=0; 

     //Calculate the Check Sum for the passed hex bytes
     foreach($hex_array as $hex=>$value)  {
           $sdc_checksum+= hexToByte($value);
     }
     return strtoupper(dechex($sdc_checksum));
   }

  /**
   * Add previx 3 for each charactor of the string]
   * @param string $string [description]
   */
   function prefix3($string){
     $prefixed_variable="";
     //Add the 30h prefix for each BCC byte
     for($i=0;$i<strlen($string);$i++){
        $prefixed_variable.=" 3".$string[$i];
     }
     return $prefixed_variable;
   }

  /**
   * @author Kamaro Lambert
   * Get the BCC or the HEX to send to SDC
   * @param string $string sum of hex bytes between 01 excluded and 05 included
   * @return string
   */
  private function getBcc($bcc){      
        $bcc_return="";
        $max= 4-strlen($bcc);

        for($counter=1;$counter<=$max;$counter++){
           $bcc_return.=" 30";
        }
      
        //adding the prefix of 30h for each bytes of the sent hex
        for($i=0;$i<strlen($bcc);$i++){
           $bcc_return.=" 3".$bcc[$i];
        }
        //Removing the prefixed space
        $bcc_return = trim($bcc_return);
        return strtoupper($bcc_return);
   }

      /**
       * @author Kamaro Lambert
       * Send receipt to SDC VIA RS232 PORT
       * RECEIPT TYPE||TRANSACTION TYPE|| RECEIPT LABEL 
       * ===========================================
       *  NORMAL     ||   SALES        ||    NS 
       *  NORMAL     ||   REFUND       ||    NR 
       *  COPY       ||   SALES        ||    CS 
       *  COPY       ||   REFUND       ||    CR 
       *  TRAINING   ||   SALES        ||    TS 
       *  TRAINING   ||   REFUND       ||    TR 
       *  PRO FORMA  ||   SALES        ||    PS 
       * 
       * -----------------------------------------------------------
       * @param  string         $Type                   Type of the receipt
       * @param  string         $mrc                   
       * @param  string         $TIN                    tax Identification Number
       * @param  string         $date_time              d/m/Y H:i:s
       * @param  integer        $receipt_number         Receipt number
       * @param  decimal(10,2)  $tax_rate_1             0.00
       * @param  decimal(10,2)  $tax_rate_2             18.00
       * @param  decimal(10,2)  $tax_rate_3             0.00
       * @param  decimal(10,2)  $tax_rate_4             0.00
       * @param  decimal(10,2)  $total_amounts_with_TAX 
       * @param  decimal(10,2)  $tax_amount_1           
       * @param  decimal(10,2)  $tax_amount_2           
       * @param  decimal(10,2)  $tax_amount_3           
       * @param  decimal(10,2)  $tax_amount_4
       *                
       * @COMMAND : C6
       * @DATA    : RtypeTTypeMRC,TIN,Date TIME, 
       *            Rnumber,TaxRate1,TaxrRate2,TaxRate3,TaxRate4,Amount1,
       *            Amount2,Amount3,Amount4,Tax1,Tax2,Tax3,Tax4
       * @EXAMPLE : nstes01012345,100600570,17/07/2013 09:29:37,
       *            1,0.00,18.00,0.00,0.00,11.00,12.00,0.00,0.00,0.00,1.83,0.00,0.00
        */
    public function sendReceipt(
          $Type="CR",
          $mrc,
          $TIN,
          $date_time,
          $receipt_number,
          $tax_rate_1="0.00",
          $tax_rate_2="18.00",
          $tax_rate_3="0.00",
          $tax_rate_4="0.00",
          $total_amounts_with_TAX,
          $tax_amount_1="0.00",
          $tax_amount_2="0.00",
          $tax_amount_3="0.00",
          $tax_amount_4="0.00")
      {
         $strinCommand =  $Receipt_type."$mrc,".$TIN.",$date_time,$receipt_number,$tax_rate_1,";
         $strinCommand .= "$tax_rate_2,$tax_rate_3,$tax_rate_4,$total_amounts_with_TAX,0.00,0.00,";
         $strinCommand .= "0.00,$tax_amount_1,$tax_amount_2,$tax_amount_3,$tax_amount_4".$ClientTin;
         $string       =$this->getCommand($strinCommand);
         
         $string_array=explode(' ',$string);

         //write the first bit
         foreach ($string_array as $string_hex_dig=>$value_dig){
           $this->device->writeByte(" ".hexToByte($value_dig)."\r\n");
         }
      
         sleep(1);

         //Send request to the SDC asking the response 
         $result =  $this->device->read();

         return $result;
      }

      function requestSignature($receipt_number=26){
        $hex_array=  strToHex($receipt_number);

        //Getting data for the receipt
        $string_for_getting_bcc = $this->getLength($hex_array).' 23 C8 '.implode(' ',$hex_array).' 05';
        $checksum_values        = explode(' ', $string_for_getting_bcc);
        
        $bcc_sum                = $this->getCheckSum($checksum_values);
        $string_dig             = '01 '.$string_for_getting_bcc.' '.$this->getBcc($bcc_sum).' 03';
          
          //$string_dig=' 01 26 23 C8 38 05 30 30 30 31 38 31 03';
         $string_array=explode(' ',$string_dig);
         //write the first bit
         foreach ($string_array as $string_hex_dig=>$value_dig){
           $this->device->writeByte(" ".hexToByte($value_dig)."\r\n");
         }
             //Send request to the SDC asking the response 
         $result =  $this->device->read();
         return $result;    
        }

      /**
       * @author Kamaro Lambert
       * Get the command to send to SDC VIA RS232 PORT
       * 
       * @param  string $string  Receipt_typemrc,TIN,date_time,receipt_number,tax_rate_1,
       * tax_rate_2,tax_rate_3,tax_rate_4,total_amounts_with_TAX,0.00,0.00,0.00,
       * tax_amount_1,tax_amount_2,tax_amount_3,tax_amount_4ClientTin
       * @return string of hex
       */
      public function getCommand($string){
         $hex_array              = strToHex($string);
         
         //Getting the BCC string and the length of the command concatenated
         $string_for_getting_bcc = $this->getLength($hex_array).' 22 C6 '.implode(' ',$hex_array).' 05';
         
         //Calculating the checksum
         $checksum_values        =explode(' ', $string_for_getting_bcc);
         
         //Get the bcc values
         $bcc_sum                = $this->getCheckSum($checksum_values);
         
         //Maked the command
         return '01 '.$string_for_getting_bcc.' '.$this->getBcc($bcc_sum).' 03';
      }
}