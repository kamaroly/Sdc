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

	/** @test receipt command */
	public function testGetCommand()
	{
		$expected = "01 24 22 C6 6E 73 74 65 73 30 31 30 31 32 33 34 35 2C 31 30 30 36 30 30 35 37 30 2C 31 37 2F 30 37 2F";
		$expected .= " 32 30 31 33 20 30 39 3A 32 39 3A 33 37 2C 31 2C 30 2E 30 30 2C 31 38 2E 30 30 2C";
		$expected .= " 30 2E 30 30 2C 30 2E 30 30 2C 31 31 2E 30 30 2C 31 32 2E 30 30 2C 30 2E 30 30 2C 30 2E 30 30 2C 30 2E";
		$expected .= " 30 30 2C 31 2E 38 33 2C 30 2E 30 30 2C 30 2E 30 30 05 31 35 3A 3D 03";
                                                                                                                                       
		$result = $this->sdcController->getCommand($this->commandString);

		$this->assertEquals($result,$expected);
	}

	/** @test Receipt Request */
	public function testSendReceipt(){
		
		$result = $this->sdcController->sendReceipt(
			'ns','tes01012345','100600570',date('d/m/Y H:i:s'),1,0.00,
			18.00,0.00,0.00,11.00,12.00,0.00,0.00,0.00,1.83,0.00,0.00
			);
        $result2 = $this->sdcController->requestSignature(1);
		dd($result2);
	}
	/** set command string */
	private function setCommandString()
	{
		$commandString = "nstes01012345,100600570,17/07/2013 09:29:37,1,0.00";
		$commandString .= ",18.00,0.00,0.00,11.00,12.00,0.00,0.00,0.00,1.83,0.00,0.00";

		$this->commandString = $commandString; 
	}
}
