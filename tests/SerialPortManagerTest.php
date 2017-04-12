<?php

namespace Kamaro\Sdc;
use Kamaro\Sdc\Devices\SerialPortManager;

class SerialPortManagerTest extends \PHPUnit_Framework_TestCase{

  protected  $ports;
  protected  $device;
  public function setUp()
  {
    $this->device = new SerialPortManager;
    $this->ports  = getPorts();
  }

  /** @test if we can open a port */
  public function testSerOpen(){
    $openPort = $this->device->open();
    $this->assertTrue($openPort);
  }

  /** @test if we can close a port */
  public function testSerClose(){
	$close = $this->device->close();
    $this->assertTrue($close === 0);
  }

  /** @testWrite */
  public function testSerWrite(){
  	$write = $this->device->write('01');
    $this->assertTrue($write === 0);
  }
}
