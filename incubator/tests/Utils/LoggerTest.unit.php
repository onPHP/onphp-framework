<?php

class LoggerTest extends UnitTestCase
{
	private $file;
	
	private $startLine 	= "start\n";
	private $testString = 'test';
	private $endLine	= "\nend\n";
	
    function __construct()
	{
        $this->UnitTestCase();
		$this->file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'test.txt';
    }
    
	function __destruct()
	{
		unlink($this->file);
	}
	
	function setUp()
	{
		$file = fopen($this->file, 'w');
		fclose($file);
	}
	
	function testWrite()
	{
		Singletone::getInstance()->Logger()
						->setLogFile($this->file)
						->setStartLine($this->startLine)
						->setEndLine($this->endLine)
						;
		Singletone::getInstance()->Logger()
						->write($this->testString)
						;
		$this->assertEqual($this->startLine
							. $this->testString
							. $this->endLine,
							file_get_contents($this->file));
	}
		
}

?>
