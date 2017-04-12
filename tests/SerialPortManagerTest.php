<?php

namespace Kamaro\Sdc;
use Kamaro\Sdc\Devices\SerialPortManager;

class SerialPortManagerTest extends \PHPUnit_Framework_TestCase
{
  /** @test if available com ports */
  public function testGetPorts()
  {
  	$ports = SerialPortManager::getPorts(); 	
  	$this->assertTrue(strContains($ports[0],'COM'));
  }

  /** @test if we can open a port */
  public function testSerOpen(){
  	$ports    = SerialPortManager::getPorts(); 
    $openPort = SerialPortManager::open($ports[0]);
    $this->assertTrue($openPort);
  }

  /** @test if we can close a port */
  public function testSerClose(){
	$close = SerialPortManager::close();
    $this->assertTrue($close === 0);
  }

  /** @testWrite */
  public function testSerWrite(){
  	$write = SerialPortManager::write('01');
    $this->assertTrue($write === 0);
  }
}
