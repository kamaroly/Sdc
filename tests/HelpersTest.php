<?php


class HelpersTest extends \PHPUnit_Framework_TestCase
{

	public function testStringContains()
	{
		$this->assertTrue(strContains('Angel Keza Kamaro','Keza'));
	}
    /**
     * Test Hex To string
     * @param  string $value
     * @return 
     */
    public function testStringBetween()
    {
    	$string = 'SDC002000173';
    	
    	$result = getStringBetween($string,'SDC','173');

        $this->assertEquals($result,'002000');
    }
    /**
     * Test that true does in fact equal true
     */
    public function testhexToByte()
    {
        $this->assertEquals(hexToByte('E5'),-27);
    }

    /**
     * Test that true does in fact equal true
     */
    public function teststToHex()
    {
    	$string = 'SDC002000173';
    	$hex    = "53 44 43 30 30 32 30 30 30 31 37 33";

    	$result = implode(" ", strToHex($string));
        $this->assertEquals($result,$hex);
    }

    /**
     * Test Hex To string
     * @param  string $value
     * @return 
     */
    public function testHexToString()
    {
    	$string = 'SDC002000173';
    	$hex    = "53 44 43 30 30 32 30 30 30 31 37 33";
    	$result = hexToStr($hex);

        $this->assertEquals($result,$string);
    }
}
