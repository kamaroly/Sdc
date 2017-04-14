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

   function __construct($port = "COM5",$licenseOwner = "KAMARO LAMBERT #1",$licenseKey = "-546625902"){

      error_reporting(E_ERROR);
      $this->device = new SerialPortManager();

     $this->device->open($port);
   }

   /**
    * Get SDC ID
    * @return string
    */
   public function getID(){
      // Hex for requesting signature
      // command = "01 24 20 E5 05 30 31 32 3E 03";
        $string_dig = $this->getSdcRequest("", "E5", "20");
       // Turn this into arry so that we can be able
       // to write byte by byte
       $string_array_dig=explode(' ',$string_dig);
             
       foreach ($string_array_dig as $string_hex_dig=>$value_dig)
       {
         $this->device->writeByte(" ".hexToByte($value_dig)."\r\n");
       }

     usleep(1);
     //Send request to the SDC asking the response 
     $string = $this->device->read();

     return substr($string, strpos($string,'SDC'), 12);  
   }

  /**
   * Get the Get SDC status
   * @return array
   */
  public function getStatus(){
       
       // Bytes for getting status
       // $string_dig= '01 24 20 E7 05 30 31 33 30 03';
       $string_dig = $this->getSdcRequest("", "E7", "24");

       $string_array_dig=explode(' ',$string_dig);
       
       foreach ($string_array_dig as $string_hex_dig=>$value_dig){
           $this->device->writeByte(" ".hexToByte($value_dig)."\r\n");
       }
      
      usleep(1);
      //Send request to the SDC asking the response 
      $str =  $this->device->read();

      $returned_data = implode(" ", strToHex($str));
      
      $returned_data = getStringBetween($returned_data,"E7","04");
      $hex_string    = str_replace(" ", "", $returned_data);
      $string        = explode(",", hexToStr($hex_string));

      $data['SDC_SERIAL_NUMBER']                      = $string[0];
      $data['FIRMWARE_VERSION']                       = $string[1];
      $data['HARDWARE_REVISION']                      = $string[2];
      $data['THE_NUMBER_OF_CURRENT_SDC_DAILY_REPORT'] = $string[3];   
      $data['LAST_REMOTE_AUDIT_DATE_TIME']            = $string[4];
      $data['LAST_LOCAL_AUDIT_DATE_TIME']             = $string[5];   

      return $data;   
  }


   /**
     * @author Lambert Kamaro
     * Number of bytes from <01> (excluded) to <05> (included) plus a fixed offset
     * 20h Length: 1 byte;
     * 
     * 
     */
    private function getLength($data){
      // Find the length by counting the data and adding length itsself, 
      // sequence,command,Post amble 05 (TOTAL=4)
      //, and 20h which is 32 in decimal which is 36 in total
      $length = hexdec(20) + 4; // 36

      if (!empty($data))
      {
          // Make sure that data is in capital letter 
          // $data = strtoupper($data);
          $byte_array = explode(' ',$data);
          $length     += count($byte_array);

          // Remove 3 bits because they are already considered
          // at the initiation
          $length = $length - 3;
      }

      return strtoupper(dechex($length));
    }

 /**
    * @author Kamaro Lambert
    * Method to get the BCC or the HEX to send to SDC
    * ===============================================
    * Check sum (0000h-FFFFh)
    * Length: 4 bytes; value: 30h - 3Fh
    * The check sum is formed by the bytes following <01> (without it) to <05>
    * included, by summing (adding) the values of the bytes. Each digit is sent as
    * an ASCII code.
    * =============================================== 
    * @param string $string sum of hex bytes between 01 excluded and 05 included
    * @return string
    */
  private function getBcc($hexString){
    
     $checkSum = 0; // This will hold the sum of values of the bytes
     // $dataArray = str_split($hexString,1);
     
     $hexArray = explode(' ',$hexString);

     //Calculate the Check Sum for the passed hex bytes
     
     foreach ($hexArray as $key => $value) {
        $ascii = base_convert($value, 16, 10);  
        $checkSum += $ascii;
     }

     //Convert to array so that we can know 
     $checkSum = dechex($checkSum);
     //How many values are left to complete
     // 4 digits bits
     $checkSumArray = str_split($checkSum,1);

     // Prefix 30
     $checkSumArray = array_map(function($value){ return '3'.$value; }, $checkSumArray);

     // Make Sure everything is capital
     $checkSumArray = array_map('strtoupper', $checkSumArray);
     // Make sure we have 4 digits
     while (count($checkSumArray) < 4 ) {
       array_unshift($checkSumArray, '30');
     }

     return implode(' ', $checkSumArray);
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
       *                
       * @command : C6
       *  @param  $string $strCommand INVOICE DETAILS (RtypeTTypeMRC,TIN,Date TIME, Rnumber,TaxRate1,TaxrRate2,TaxRate3,TaxRate4,Amount1,Amount2,Amount3,Amount4,Tax1,Tax2,Tax3,Tax4)
       * @example NS01012345,100600570,11/05/2016 12:35:20,23,0.00,18.00,0.00,0.00,11.00,12.00,0.00,0.00,0.00,1.83,0.00,0.00
       * @return array
    */
    public function sendReceiptData($strCommand){
         $this->device->flush();
         // Build command
         $command      = $this->getSdcRequest($strCommand,"C6","23");
         $string_array = explode(' ',$command);
         //write the first bit
         foreach ($string_array as $string_hex_dig=>$value_dig){
           $this->device->writeByte(" ".hexToByte($value_dig)."\r\n");
         }
      
         usleep(600000);
         //Send request to the SDC asking the response 
         $response = $this->device->read();    
         $this->errorHandling($response);
         // We have no error, then let's format the response
         $returned_data = implode(" ", strToHex($response));     

         $returned_data = getStringBetween($returned_data,"C6","04");

         return hexToStr($returned_data);
      }

      /**
       * Get signature from SDC
       * @author  : Kamaro Lambert 
       * @COMMAND : C8
       * @param   : INVOICE NUMBER
      */
      public function getSignature($receipt_number){
         $this->device->flush();
         //Getting data for the receipt
         // $string_dig=' 01 26 23 C8 38 05 30 30 30 31 38 31 03';
         $returned_data = null;
         // Make sure we get proper response
         
         $string_dig   = $this->getSdcRequest($receipt_number, "C8", "21");
         $string_array = explode(' ',$string_dig);

         //write the first bit
         foreach ($string_array as $string_hex_dig=>$value_dig){
           $this->device->writeByte(" ".hexToByte($value_dig)."\r\n");
         }

         usleep(60000);
         //Send request to the SDC asking the response 
         $response = $this->device->read();    
         $this->errorHandling($response);
         // We have no error, then let's format the response
         $returned_data = implode(" ", strToHex($response));     
         $returned_data = getStringBetween($returned_data,"C8","04");

         $hex_string    = str_replace(" ", "", $returned_data);
         $cleanResponse = explode(",", hexToStr($hex_string));

        // CLEAN RESPONSE
        $dataTime                   = explode(' ', $cleanResponse[4]);
        $data['date_time']          = implode(' ', $dataTime);
        $data['SDC_ID']             = $cleanResponse[0];
        $data['SDC_RECEIPT_NUMBER'] = $cleanResponse[1].'/'.$cleanResponse[2].'/'.$cleanResponse[3];
        $data['INTERNAL_DATA']      = implode('-', str_split($cleanResponse[6],4));
        $data['RECEIPT_SIGNATURE']  = implode('-', str_split($cleanResponse[5],4));   

        var_dump($data);
        return $data;
      }
      
      /**
      * @author : Kamaro Lambert 
      * Send electronic journal to RRA
      * @COMMAND : EE
      * Current line type:
      * ====================
      * 'B' mark for begin of the receipt
      * 'N' mark for line into the body of receipt
      * 'E' mark for end of receipt 
      *  Read the file and display it line by line.
      *
      * @param $line receipt line to send to SDC
      * @param $sequence a number between 32 and 127
      */
      public function sendElectronicJournal($line,$sequence)
      {
        if ($sequence < 32 || $sequence > 127) {
          throw new Exception("Invalid sequence it has to be between 32 and 127", 1);
        }
         $sequence = dechex($sequence);
         // Build command
         $command      = $this->getSdcRequest($line,"EE",$sequence);
         $string_array = explode(' ',$command);
         //write the first bit
         foreach ($string_array as $string_hex_dig=>$value_dig){
           $this->device->writeByte(" ".hexToByte($value_dig)."\r\n");
         }

         usleep(130000);
         //Send request to the SDC asking the response 
         
         $response = $this->device->read();    
         $this->errorHandling($response);
         // We have no error, then let's format the response
         $returned_data = implode(" ", strToHex($response));     

         $returned_data = getStringBetween($returned_data,"EE","04");

         return hexToStr($returned_data);
      }


       /**
        * @author  : Kamaro Lambert 
        * 
        * @COMMAND : C9
        * Request recept counters from SDC
        * @author  : Kamaro Lambert 
        * @param  int $invoiceNumber 
        * @return $string
        */
      public function getCounters($invoiceNumber)
      {
         $this->device->flush();

         // Build command
         $command      = $this->getSdcRequest($strCommand,"C9","27");
         $string_array = explode(' ',$command);

         //write the first bit
         foreach ($string_array as $string_hex_dig=>$value_dig){
           $this->device->writeByte(" ".hexToByte($value_dig)."\r\n");
         }
      
         usleep(1);
         //Send request to the SDC asking the response 
         return $this->device->read();
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

       /**
        * Get SDC request method
        * @Author Kamaro Lambert
        * @param string data //  RtypeTTypeMRC,TIN,Date TIME, Rnumber,TaxRate1,TaxrRate2,TaxRate3,TaxRate4,Amount1,Amount2,Amount3,Amount4,Tax1,Tax2,Tax3,Tax4
        *                    // Example : "nstes01012345,100600570,17/07/2013 09:29:37,1,0.00,18.00,0.00,0.00,11.00,12.00,0.00,0.00,0.00,1.83,0.00,0.00"
        * @param string command // Command to sdc example c6
        */
        public function getSdcRequest($data = null,$command,$sequence = 20)
        {
            // Make sure ALL are in  caps
            // Prepare Data
            $data    = strtoupper($data);
            $hexData = $this->getHexData($data);

            // Prepare command
            $request = $sequence . " " . strtoupper($command) . " 05";
            
            // if the data is not empty then add it to the command
            if (!empty($data)){
                $request = $sequence . " " . strtoupper($command) . " " . $hexData . " 05";
            }
            // dd($request);
            // Get the length of the byte hex to be sent
            $commandLength = $this->getLength($request);
              
            // Add length to the request
            $request = $commandLength.' '.$request;

            // Get checksum(BCC) of this command
            $commandBcc = $this->getBcc( $request);

            // For example to look for serial number you have to pass "01 24 20 E5 05 30 31 32 3E 03" OR 
            // SDC Status "01 24 20 E7 05 30 31 33 30 03"
            $request = "01 ".$request.' '.$commandBcc ." 03";
            
            // Make sure we remove any double spaces 
            return  str_replace('  ', ' ', trim($request));
        }

      /**
       * Get Hexa Data from the string as per RRA definition
       * @param  $string
       * @return string
       */
      public function getHexData($string)
      {
        // Convert to Bytes array
        $byte_array = unpack('C*', $string);
        
        // Get Ascii equivalent of the 
        $bytes      = array_map("chr", $byte_array);

        // Get Hex equivalent of the ascii
        $bytes      = strtoupper(bin2hex(implode($bytes)));

        return implode(' ',str_split($bytes,2));
      }

        /*
        ERROR CODES
        =====================================
        i.       00 – no error;
        ii.      11 – internal memory full;
        iii.     12 – internal data corrupted;
        iv.      13 – internal memory error;
        v.       20 – Real Time Clock error;
        vi.      30 – wrong command code;
        vii.     31 – wrong data format in the CIS request data;
        viii.    32 – wrong TIN in the CIS request data;
        ix.      33 – wrong tax rate in the CIS request data;
        x.       34 – invalid receipt number int the CIS request data;
        xi.      40 – SDC not activated;
        xii.     41 – SDC already activated;
        xiii.    90 – SIM card error;
        xiv.     91 – GPRS modem error;
        xv.      99 – hardware intervention is necessary.

        ------------------------------------------
        WORNING CODES
        ======================================
        i. 0 – no warning;
        ii. 1 – SDC internal memory is near to full (it is at more than 90% of capacity);
        iii. 2 – SDC internal memory is near to full (it is at more than 95% of capacity).
        -------------------------------------------

         * @param  string missage_string    [description]
         * @return [type]         [description]
        */

          function errorHandling($missage_string)
          {

            if(strpos($missage_string, "P") !== FALSE)
            {
              return true;
            }
            if(strpos($missage_string, "E11") !== FALSE)
            {
               throw new Exception('sdc_error_internal_memory_full', 1);
            }
            if(strpos($missage_string, "E12") !== FALSE)
            {
               throw new Exception('sdc_error_internal_data_corrupted', 1);
            }
            if(strpos($missage_string, "E13") !== FALSE)
            {
               throw new Exception('sdc_error_internal_memory_error', 1);
            }
            if(strpos($missage_string, "E20") !== FALSE)
            {
               throw new Exception('sdc_error_real_Time_Clock_error', 1);
            }
            if(strpos($missage_string, "E30") !== FALSE)
            {
               throw new Exception('sdc_error_wrong_command_code', 1);
            }
            if(strpos($missage_string, "E31") !== FALSE)
            {
               throw new Exception('sdc_error_wrong_data_format_in_the_CIS_request_data', 1);
            }
            if(strpos($missage_string, "E32") !== FALSE)
            {
               throw new Exception('sdc_error_wrong_TIN_in_the_CIS_request_data', 1);
            }
            if(strpos($missage_string, "E33") !== FALSE)
            {
               throw new Exception('sdc_error_wrong_tax_rate_in_the_CIS_request_data', 1);
            }
            if(strpos($missage_string, "E34") !== FALSE)
            {
               throw new Exception('sdc_error_invalid_receipt_number_int_the_CIS_request_data', 1);
            }
            if(strpos($missage_string, "E40") !== FALSE)
            {
               throw new Exception('sdc_error_sdc_not_activated', 1);
            }
            if(strpos($missage_string, "E41") !== FALSE)
            {
               throw new Exception('sdc_error_sdc_already_activated', 1);
            }
            if(strpos($missage_string, "E90") !== FALSE)
            {
               throw new Exception('sdc_error_sim_card_error', 1);
            }
            if(strpos($missage_string, "E91") !== FALSE)
            {
               throw new Exception('sdc_error_gprs_modem_error', 1);
            }
            if(strpos($missage_string, "E92") !== FALSE)
            {
               throw new Exception('sdc_error_hardware_intervention_is_necessary', 1);
            }

            return 'Unknown ERROR:'.$missage_string;
         }
}