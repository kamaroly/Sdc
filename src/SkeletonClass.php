<?php

namespace League\Skeleton;
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

Class Sdc_controller 
{
  public $sdc_connected=FALSE;
  public $sdc_port     =FALSE;

/**
 * [_send_journal description]
 * @param  array  $receipt_string_array [description]
 * @return [type]                       [description]
 */
  function _send_journal($receipt_string_array=array())
  {


//opening the port
$this->_open_port();
//Setting the sequence
$sequence="24";

//Initiating the counter that will help us to switch sequences
$counter=0;

//Now go through each line of the receipt
foreach ($receipt_string_array as $key => $value)
 {

  if ($counter%2==0) {
    $sequence="24";
  }
  else
  {
    $sequence="25";
  }
  $receipt_linedata=strval( $value);
  $hex_array=$this->strToHex($receipt_linedata);

 //Getting data for the receipt
 $string_for_getting_bcc= $this->get_length($hex_array)." $sequence EE ".implode(" ",$hex_array)." 05";
 

 $checksum_values=explode(' ', $string_for_getting_bcc);

 
 $bcc_sum= $this->_get_checsum_value($checksum_values);

 $string_dig= '01 '.$string_for_getting_bcc.' '.$this->_get_bcc($bcc_sum).' 03';
  
      $string_array_dig=explode(' ',$string_dig);
       $bytes_dig=" ";        
       foreach ($string_array_dig as $string_hex_dig=>$value_dig)
       {
         $bytes_dig=" ".(('0x'.$value_dig+128) % 256) - 128;
         if($bytes_dig!=00)
         {
         ser_writebyte("$bytes_dig\r\n");
         }
       } 
     //Send request to the SDC asking the response 
    $str = ser_read();
   }

   $this->_close_port();
}

  /**
   * method to get the sdc_id
   * @return [type] [description]
   */
  function get_sdc_id()
  {
     $this->_open_port();

      $string_dig="01 24 20 E5 05 30 31 32 3E 03";
   
      $string_array_dig=explode(' ',$string_dig);
       $bytes_dig=" ";        
       foreach ($string_array_dig as $string_hex_dig=>$value_dig)
       {
         $bytes_dig=" ".(('0x'.$value_dig+128) % 256) - 128;
         if($bytes_dig!=00)
         {
         ser_writebyte("$bytes_dig\r\n");
         }
       }

     sleep(1);
     //Send request to the SDC asking the response 
   return $str = ser_read();
  }

  /**
   * @method to get the get_sdc_status
   * @return [type] [description]
   */
  function get_sdc_status()
  {
     $this->_open_port();

    $string_dig= '01 24 20 E7 05 30 31 33 30 03';
   
      $string_array_dig=explode(' ',$string_dig);
       $bytes_dig=" ";        
       foreach ($string_array_dig as $string_hex_dig=>$value_dig)
       {
         $bytes_dig=" ".(('0x'.$value_dig+128) % 256) - 128;
         if($bytes_dig!=00)
         {
         ser_writebyte("$bytes_dig\r\n");
         }
       }
       sleep(1);
     //Send request to the SDC asking the response 
    $str = ser_read();
     
    if($this->_error_handling($str))
    {

      $returned_data=implode(" ", $this->strToHex($str));
      

      $returned_data=$this->_get_string_between($returned_data,"E7","04");
      
      $hex_string=str_replace(" ", "", $returned_data);

      $string=explode(",", $this->hexToStr($hex_string));
      $data['SDC serial number']                     =$string[0];
      $data['Firmware version']                      =$string[1];
      $data['Hardware revision']                     =$string[2];
      $data['The number of current SDC daily report']=$string[3];   
      $data['Last remote audit date and time']       =$string[4];
      $data['Last local audit date and time']        =$string[5];                 
      $this->session->set_userdata('sdc_status', $data);
    }
    else
    {
      return FALSE;
    }
    
    $this->_close_port();
    
    
  }
  /**
   * @method to get datetime 
   * @param  string $command [description]
   * @return [type]          [description]
   */
function get_sdc_date_time($command="3E")
  {
 $this->_open_port();
//Getting data for the receipt
$string_for_getting_bcc= "24 21 ".$command." 05";;


$checksum_values=explode(' ', $string_for_getting_bcc);


$bcc_sum= $this->_get_checsum_value($checksum_values);

$string_dig= '01 '.$string_for_getting_bcc.' '.$this->_get_bcc($bcc_sum).' 03';

   
      $string_array_dig=explode(' ',$string_dig);
       $bytes_dig=" ";        
       foreach ($string_array_dig as $string_hex_dig=>$value_dig)
       {
         $bytes_dig=" ".(('0x'.$value_dig+128) % 256) - 128;
         if($bytes_dig!=00)
         {
         ser_writebyte("$bytes_dig\r\n");
         }
       }
       sleep(1);
     //Send request to the SDC asking the response 
    $str = ser_read();
    
    if($this->_error_handling($str))
    {
      
      $returned_data=implode(" ", $this->strToHex($str));

      
      $returned_data=$this->_get_string_between($returned_data,$command,"04");
      
      $hex_string=str_replace(" ", "", $returned_data);

      $string=explode(",", $this->hexToStr($hex_string));


      $data['Current date and time of SDC']                     =$string[0];
     
      return $data;     
   
    }
    else
    {
      return FALSE;
    }
    
    $this->_close_port();
    
    
  }
  
  /**
   * [getsignature description]
   * @param  string  $Receipt_type   [description]
   * @param  integer $receipt_number [description]
   * @return [type]                  [description]
   */
function getsignature($Receipt_type="NS",$receipt_number=26)
  {
  
    
    //Open sdc set port
    $connect_sdc=$this->_open_port();
    
   
    //Confirm is sdc is connected before printing
    if($connect_sdc===FALSE)
    {
       //SDC not connected , report the error
       $this->sale_lib->set_sale_sdc_error($this->lang->line('sales_sdc_not_connected'));
       
       //redirect to the sales module with error
       redirect('sales',"refresh");

    }
 
    $Receipt_type=$this->sale_lib->get_mode();
    //Setting the current receipt number
     $receipt_number=(strtolower(substr($Receipt_type, 0,1))!='p')?$this->Sale->get_max_sale_id():$this->Sale_quotations->get_max_sale_id();
       

     //sending receipt data
     $response= $this->receipt_to_sdc($Receipt_type,$receipt_number);

    $result=$this->_error_handling($response);
     if($result!==TRUE)
     {
       //Error detected , Set it and return to the page
       $this->sale_lib->set_sale_sdc_error($result);
       
         //redirect to the previous url module with error
     redirect($this->uri->uri_string(),"refresh");
     }
     
    //Requesting Signature
    $sdc_signature_request=$this->request_sign($receipt_number);

    //Check if there is an error
    $result=$this->_error_handling($sdc_signature_request);

     if($result!==TRUE)
     {
       //Error detected , Set it and return to the page
       $this->sale_lib->set_sale_sdc_error($result);
        //redirect to the previous url module with error
       
       redirect($this->uri->uri_string(),"refresh");
     }

    $sdc_data=explode(',',$sdc_signature_request);
   
    //Get Current salesman information 
    $employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
      
    $emp_info=$this->Employee->get_info($employee_id);
     
    $data['employee']=$emp_info->first_name.' '.$emp_info->last_name;


    //Get Customer if he exists if he doesn't -1 will be returned
    $customer_id=$this->sale_lib->get_customer();

    $cust_info=$this->Customer->get_info($customer_id);
    
    $data['customer_tin']=$cust_info->tin;
    $data['payments']=$this->sale_lib->get_payments();
   
    $data['receipt_number']               =$receipt_number;
    $data['cart']                         =$this->sale_lib->get_cart();
    $data['total']                        =round($this->sale_lib->get_total());
    $data['subtotal']                     =round($this->sale_lib->get_subtotal());
    $data['taxes']                        =$this->sale_lib->get_taxes();
    
    $data['MRC']                          = $this->config->item('software_developer_id').$this->config->item('software_certificate_number').$this->config->item('serial_number');
    $data['SDC_ID']=$sdc_info['SDC_ID']   = substr($sdc_data[0],4);
    $data['TNumber']=$sdc_info['TNumber'] = $sdc_data[1];
    $data['GNumber']=$sdc_info['GNumber'] = $sdc_data[2];
    $data['RLabel']=$sdc_info['RLabel']   = $sdc_data[3];

    $date_time                            =explode(' ',$sdc_data[4]);

    $data['Date']=$sdc_info['Date']       = $date_time[0];
    $data['TIME']=$sdc_info['TIME']       = $date_time[1];
    $data['Receipt_Signature']=$sdc_info['Receipt_Signature']      = $this->str_insert_dash($sdc_data[5],4);

    $internal                      =explode('.',$sdc_data[6]);
    $data['Internal_Data']=$sdc_info['Internal_Data']              =$this->str_insert_dash((substr($internal[0], 0,strlen($internal)-12)),4);

    $this->_close_port();
    
    $this->sale_lib->set_sdc_receipt_data($sdc_info);


    $receipt_string=$this->load->view('sdc/Normal_sales',$data,TRUE);
    
    $receipt_to_display=str_replace('endline', '<br/>',  $receipt_string);

    $this->session->set_userdata('electronic_journal',$receipt_to_display);
   
    $receipt_array=explode('endline', $receipt_string);


    if (strlen($sdc_data[5])===strlen("JVPGPLYGDJRSMXF2") AND (  $data['RLabel'] =="NS" OR $data['RLabel'] =="NR"))
     {
      #setting the sales_id
      if($this->sale_lib->get_sale_id()==-1)
      {
         $this->sale_lib->set_sale_id($receipt_number);
      }
     
      # code...
    $this->_send_journal($receipt_array);
    }
    //Go back to the previous url
    
    redirect($this->uri->uri_string(),"refresh");
    
  }


function request_sign($receipt_number=26)
{


$hex_array=$this->strToHex($receipt_number);

//Getting data for the receipt
$string_for_getting_bcc= $this->get_length($hex_array).' 23 C8 '.implode(' ',$hex_array).' 05';

$checksum_values=explode(' ', $string_for_getting_bcc);

$bcc_sum= $this->_get_checsum_value($checksum_values);

$string_dig= '01 '.$string_for_getting_bcc.' '.$this->_get_bcc($bcc_sum).' 03';
  
  //$string_dig=' 01 26 23 C8 38 05 30 30 30 31 38 31 03';
      $string_array_dig=explode(' ',$string_dig);
       $bytes_dig=" ";        
       foreach ($string_array_dig as $string_hex_dig=>$value_dig)
       {
         $bytes_dig=" ".(('0x'.$value_dig+128) % 256) - 128;
         if($bytes_dig!=00)
         {
         ser_writebyte("$bytes_dig\r\n");
         }
       }
       sleep(1);
     //Send request to the SDC asking the response 
    $str = ser_read();
    
    
    return $str;
    
}
/**
 * [receipt_to_sdc description]
 * @param  string $Receipt_type   [description]
 * @param  [type] $receipt_number [description]
 * @return [type]                 [description]
 */
function receipt_to_sdc($Receipt_type="NS",$receipt_number)
  {

     ser_flush(true,true);
    
     $string=$this->get_command($Receipt_type,$receipt_number);
     
     $string_array=explode(' ',$string);
       $bytes=" ";
       
       //write the first bit
       // var_dump($string_array);           
       foreach ($string_array as $string_hex=>$value)
       {
         $bytes=" ".(('0x'.$value+128) % 256) - 128;
         ser_writebyte("$bytes\r\n");
       }
  sleep(1);
   //getting the response from SDC
   
   return ser_read();
   
  
  
  }


  /**
 * @author Kamaro Lambert
 * @method to get the command to send to SDC VIA RS232 PORT
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
 * */
  
function get_command($Receipt_type="CR",$receipt_number=26)
{

//Loading libraries 
$this->load->library('sale_lib');


 //Company TIN
 $TIN=$this->config->item('tin');

 //Client TIN
 $ClientTin=","."123456789";


//Getting all taxes
$taxes=$this->sale_lib->get_taxes();

//Getting the tax rate 
$tax_rate_1="0.00";
$tax_rate_2="18.00";
$tax_rate_3="0.00";
$tax_rate_4="0.00";


//Getting the tax amount
$tax_amount_1="0.00";
$tax_amount_2="0.00";
$tax_amount_3="0.00";
$tax_amount_4="0.00";

//is there item with tax in the cart?
if(count($taxes)>0)
{ //Yes Item found now let's change tax rate 2 and it's amount
  $tax_amount_2=number_format(array_sum($taxes),2,".","");
}

//Setting Date time
 $date_time=DATE("d/m/Y H:i:s");

//Total amount with taxes
$total_amounts_with_TAX=number_format($this->sale_lib->get_total(),2,".","");

//Getting the receipt number
$receipt_number=intval($receipt_number);

//Machine Registration Code
$mrc=$this->config->item('software_developer_id').$this->config->item('software_certificate_number').$this->config->item('serial_number');
//Generating the string with data
$variable=$Receipt_type."$mrc,".$TIN.",$date_time,$receipt_number,$tax_rate_1,$tax_rate_2,$tax_rate_3,$tax_rate_4,$total_amounts_with_TAX,0.00,0.00,0.00,$tax_amount_1,$tax_amount_2,$tax_amount_3,$tax_amount_4".$ClientTin;

  //Initiate the variable to hold the command data
  $command_data="";


       $hex_array=$this->strToHex($variable);
       
       //Getting the BCC string and the length of the command concatenated
       $string_for_getting_bcc= $this->get_length($hex_array).' 22 C6 '.implode(' ',$hex_array).' 05';

       //Calculating the checksum
       $checksum_values=explode(' ', $string_for_getting_bcc);

       //Get the bcc values
       $bcc_sum= $this->_get_checsum_value($checksum_values);

       //Maked the command
      $command_data.='01 '.$string_for_getting_bcc.' '.$this->_get_bcc($bcc_sum).' 03';

return $command_data;

}




/**
 * @author Kamaro Lambert
 * @method to get the length of the command before passing it to sdc
 * */
function get_length($hex_data_array=array())
{
  
 //return false if nothing was passed as argument
  if(empty($hex_data_array))
  {
    return false;
  }


 //find the length by countind the data and adding leng itsself mseq,cmd,05=4, and 20h which is 32 in decimal
 $length= dechex(count($hex_data_array)+4+32);

 return strtoupper($length);
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
  public function hexToStr($hex)
  {
    //Initializing the variable
    $string='';
    //Convert each HEX to string
    for ($i=0; $i < strlen($hex)-1; $i+=2)
    {
      $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
  }

/**
 * @Author Kamaro Lambert
 * @method to put dash between every n character in a given string
 * @param string $string
 * @param integer $number_of_charaters
 */
function str_insert_dash($string,$number_of_charaters=3) 
{
    return implode("-", str_split($string, $number_of_charaters));
}

   
   /**
   * @Author Kamaro Lambert
   * Method to get the BCC or the HEX to send to SDC
   * @param string $string sum of hex bytes between 01 excluded and 05 included
   * @return string
   */
  
  public function _get_bcc($bcc)
   {


      
      $bcc_return=""; //Initializing the variable
          
          $max=4-strlen($bcc);
          for($counter=1;$counter<=$max;$counter++)
          {
             $bcc_return.=" 30";
          }
        
          //adding the prefix of 30h for each bytes of the sent hex
          for($i=0;$i<strlen($bcc);$i++)
           {
             $bcc_return.=" 3".$bcc[$i];
           }
            //Removing the prefixed space
          $bcc_return=substr($bcc_return, 1);

      return   strtoupper($bcc_return);
  }
  
  /**
   * @author Kamaro Lambert
   * Method to get the CheckSum for the hex bytes between 01 excluded and 05 included
   * @param array $hex_array
   * @return string
   */
  public function _get_checsum_value($hex_array=array())
   {
    

     //Check if the array has some value
     if(count($hex_array>0))
       {
        //Initiating the checksum variable
        $sdc_checksum=0; 

        //Calculate the Check Sum for the passed hex bytes
        foreach($hex_array as $hex=>$value)  
         {
          $sdc_checksum+= hexdec($value);
         }
       }
       else
       {
        return false;
       }
      
      //Return the CheckSum
     return strtoupper(dechex($sdc_checksum));
   }

  /**
   * [_add_prefix_3 to add previx 3 for each charactor of the string]
   * @param string $variable [description]
   */
   function _add_prefix_3($variable=null)
   {
    if(!$variable)
    {
      return false;
    }
    $prefixed_variable="";
    //Add the 30h prefix for each BCC byte
         for($i=0;$i<strlen($variable);$i++) 
          {
           $prefixed_variable.=" 3".$variable[$i];
          }
     return $prefixed_variable;
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

  function _error_handling($missage_string)
  {

    if(strpos($missage_string, "P0"))
    {
      return true;
    }
    elseif(strpos($missage_string, "E11"))
    {
      return $this->lang->line('sdc_error_internal_memory_full');
    }
    elseif(strpos($missage_string, "E12"))
    {
      return $this->lang->line('sdc_error_internal_data_corrupted');
    }
    elseif(strpos($missage_string, "E13"))
    {
      return $this->lang->line('sdc_error_internal_memory_error');
    }
    elseif(strpos($missage_string, "E20"))
    {
      return $this->lang->line('sdc_error_real_Time_Clock_error');
    }
    elseif(strpos($missage_string, "E30"))
    {
      return $this->lang->line('sdc_error_wrong_command_code');
    }
    elseif(strpos($missage_string, "E31"))
    {
      return $this->lang->line('sdc_error_wrong_data_format_in_the_CIS_request_data');
    }
    elseif(strpos($missage_string, "E32"))
    {
      return $this->lang->line('sdc_error_wrong_TIN_in_the_CIS_request_data');
    }
    elseif(strpos($missage_string, "E33"))
    {
      return $this->lang->line('sdc_error_wrong_tax_rate_in_the_CIS_request_data');
    }
    elseif(strpos($missage_string, "E34"))
    {
      return $this->lang->line('sdc_error_invalid_receipt_number_int_the_CIS_request_data');
    }
    elseif(strpos($missage_string, "E40"))
    {
      return $this->lang->line('sdc_error_sdc_not_activated');
    }
    elseif(strpos($missage_string, "E41"))
    {
      return $this->lang->line('sdc_error_sdc_already_activated');
    }
    elseif(strpos($missage_string, "E90"))
    {
      return $this->lang->line('sdc_error_sim_card_error');
    }
    elseif(strpos($missage_string, "E91"))
    {
      return $this->lang->line('sdc_error_gprs_modem_error');
    }
     elseif(strpos($missage_string, "E92"))
    {
      return $this->lang->line('sdc_error_hardware_intervention_is_necessary');
    }
     elseif(strlen($missage_string)>110)
    {
      return str_replace('_SDC_PORT_', $this->config->item('sdc_port'), $this->lang->line('sales_sdc_not_connected_to_the_port'));
    }
   elseif(empty($missage_string) or strlen($missage_string)==0 )
    {
      return str_replace('_SDC_PORT_', $this->config->item('sdc_port'), $this->lang->line('sales_sdc_not_connected_to_the_port'));
    }
    elseif(strlen($missage_string)==1 )
    {
         return $this->lang->line('sdc_is_busy');
       }
    else
    {
      return "Unknow error :".$missage_string;
    }

 }

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


/**
     * @author Kamaro Lambert
     * @method to close  port
     */
  function _close_port()
  {
    ser_close();
  }
  
   /**
     * @author Kamaro Lambert
     * @method to open a port
     */
  function _open_port($port = 'COM5')
  {
    
    if(substr($port, 3)>9)
    {
      $port="\\\\.\\$port";
    }

    ser_register("KAMARO LAMBERT #1","-1054384780");

     //Opening Port...
   ser_open($port, 9600, 8, "None", 1, "None");
     
    // Clears input and/or output buffers.
    ser_flush(true,true);
 
   //Check if the port is open 
   return ser_isopen();
  }
  
}