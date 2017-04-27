<?php

namespace Kamaro\Sdc;
use Kamaro\Sdc\SDCController;

class SDCControllerTest extends \PHPUnit_Framework_TestCase{

	private $commandString = null;

	private $invoiceNumber = 10100;

	public function setUp()
	{
		$this->sdcController = new SDCController('COM8');
		
		// $this->invoiceNumber = random_int(100,1000);
		$this->setCommandString();

	}

	public function testGetSdcREquest()
	{
		$expected = "01 24 20 E5 05 30 31 32 3E 03";
		$results  = $this->sdcController->getSdcRequest(null,'E5',20);
		$this->assertEquals($expected,$results);
	}

	/** @test if we can get ID *8
	public function testGetSdcId(){
		$sdcId = $this->sdcController->getID();
		$this->assertTrue(strContains($sdcId,'SDC'));
	}

	/** @test if we can get sdc status */
	public function testGetStatus()
	{
		$status = $this->sdcController->getStatus();

		$data[] = 'SDC_SERIAL_NUMBER';
		$data[] = 'FIRMWARE_VERSION';
		$data[] = 'HARDWARE_REVISION';
		$data[] = 'THE_NUMBER_OF_CURRENT_SDC_DAILY_REPORT';
		$data[] = 'LAST_REMOTE_AUDIT_DATE_TIME';
		$data[] = 'LAST_LOCAL_AUDIT_DATE_TIME';

		foreach ($data as $key => $value) {
			$this->assertTrue(array_key_exists($value, $status));
		}
		
	}

	/** @test receipt command */
	public function testGetgetSdcRequest()
	{
		$expected = '01 8E 23 C6 4E 53 30 31 30 31 32 33 34 35 2C 31 30 30 36 30 30 35 37 30 2C 31 31 2F 30 35 2F 32 30 31 36 20 31 32 3A 33 35 3A 32 30 2C 32 33 2C 30 2E 30 30 2C 31 38 2E 30 30 2C 30 2E 30 30 2C 30 2E 30 30 2C 31 31 2E 30 30 2C 31 32 2E 30 30 2C 30 2E 30 30 2C 30 2E 30 30 2C 30 2E 30 30 2C 31 2E 38 33 2C 30 2E 30 30 2C 30 2E 30 30 05 31 35 3A 3A 03';
       
        $command = "NS01012345,100600570,11/05/2016 12:35:20,23,0.00,18.00,0.00,0.00,11.00,12.00,0.00,0.00,0.00,1.83,0.00,0.00";                                                                    
		$result = $this->sdcController->getSdcRequest($command,"C6","23");

		$this->assertEquals($expected,$result);
	}

	/** @test Receipt Request */
	public function testSendReceipt(){
		$result = $this->sdcController->sendReceiptData($this->commandString);
		$this->assertTrue(strContains($result,'P'));
	}

	/** Test get Encounter */
	public function testGetSignature()
	{
		$result  = $this->sdcController->getSignature($this->invoiceNumber);

		$data[] = 'SDC_ID';
		$data[] = 'SDC_RECEIPT_NUMBER';
		$data[] = 'INTERNAL_DATA';
		$data[] = 'RECEIPT_SIGNATURE';

		foreach ($data as $key => $value) {
			$this->assertTrue(array_key_exists($value, $result));
		}

	}

	/** @test send eletronic journal */
	public function testSendElectonicJournal()
	{
		$receipt = __DIR__.'/stubs/A8.txt';
		$lines   = file($receipt);
		$endLine = count($lines) - 1;

		$sequence = 32;

		foreach ($lines as $key => $line) {
		 // Update sequnce
		    if ($sequence > 127) {
				$sequence = 32;
			}

			switch ($key) {
				case 0:
					$results = $this->sdcController->sendElectronicJournal('B'.$line,$sequence);
					break;
				case $endLine:
					$results = $this->sdcController->sendElectronicJournal('E'.$line,$sequence);
					break;				
				default:
					$results = $this->sdcController->sendElectronicJournal('N'.$line,$sequence);
					break;
			}
			$sequence++;
			$this->assertTrue(strContains($results,'P'));
		}
	}

	/** test get counters */
	public function testGetCounters()
	{
		$counters = $this->sdcController->getCounters($this->invoiceNumber);
	}

	// /** set command string */
	private function setCommandString()
	{
		$s = "NS01012345,100600570,11/05/2016 12:35:20,".$this->invoiceNumber .",0.00,18.00,0.00,0.00,11.00,12.00,0.00,0.00,0.00,1.83,0.00,0.00";

		$this->commandString = $s; 
	}
}
