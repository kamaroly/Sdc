<?php

namespace Kamaro\Sdc;
use Kamaro\Sdc\SDCController;

class SDCControllerTest extends \PHPUnit_Framework_TestCase{

	private $commandString = null;

	public function setUp()
	{
		$this->sdcController = new SDCController;
		$this->setCommandString();

	}

	public function testGetSdcREquest()
	{
		$expected = "01 24 20 E5 05 30 31 32 3E 03";
		$results  = $this->sdcController->getSdcRequest(null,'E5',20);

		$this->assertEquals($expected,$results);
	}
	/** @test if we can get ID */
	public function testGetSdcId(){
		$sdcId = $this->sdcController->getID();
		$this->assertTrue(strContains($sdcId,'SDC'));
	}

	/** @test if we can get sdc status */
	public function testGetStatus()
	{
		$sdcId = $this->sdcController->getID();
		$status = $this->sdcController->getStatus();
		
		$this->assertTrue(strContains($status,$sdcId));
	}

	// /** @test receipt command */
	// public function testGetCommand()
	// {
	// 	$expected = '01 92 23 C6 4E 53 54 45 53 30 31 30 31 32 33 34 35 2C 31 30 30 36 30 30 35 37 30 2C 31 37 2F 30 37 2F 32 30 31 33 20 30 39 3A 32 39 3A 33 37 2C 31 35 2C 30 2E 30 30 2C 31 38 2E 30 30 2C 30 2E 30 30 2C 30 2E 30 30 2C 31 31 2E 30 30 2C 31 32 2E 30 30 2C 30 2E 30 30 2C 30 2E 30 30 2C 30 2E 30 30 2C 31 2E 38 33 2C 30 2E 30 30 2C 30 2E 30 30 0A 05 31 36 3B 3B 03';
                                                                                                              
	// 	$result = $this->sdcController->getHexData($this->commandString);

	// 	$this->assertEquals($result,$expected);
	// }

	// /** @test Receipt Request */
	// public function testSendReceipt(){
		
	// 	$result = $this->sdcController->sendReceipt(
	// 		'ns','tes01012345','100600570',date('d/m/Y H:i:s'),1021,0.00,
	// 		18.00,0.00,0.00,11.00,12.00,0.00,0.00,0.00,1.83,0.00,0.00
	// 		);
 //        $result2 = $this->sdcController->requestSignature(1);

	// }
	// /** set command string */
	private function setCommandString()
	{
		$s = "nstes01012345,100600570,17/07/2013 09:29:37,15,0.00,18.00,0.00,0.00,11.00,12.00,0.00,0.00,0.00,1.83,0.00,0.00";

		$this->commandString = $s; 
	}
}
