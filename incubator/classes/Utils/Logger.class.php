<?php
/*$Id$*/

/**
*
*	Logs errors into file
*
**/
class Logger extends Singleton
{
	const DEFINE_LOG_FILE_FIRST = 'You should define log file name first';
	
	private $logFile 	= null;
	private $startLine	= '';
	private $endLine	= '';
	
	public function setLogFile($file)
	{
		$this->logFile = $file;
		
		return $this;
	}
	
	public function setStartLine($line)
	{
		$this->startLine = $line;
		
		return $this;
	}
	
	public function setEndLine($line)
	{
		$this->endLine = $line;
		
		return $this;
	}
	
	public function getLogFile()
	{
		return $this->logFile;
	}
	
	public function getStartLine()
	{
		return $this->startLine;
	}
	
	public function getEndLine()
	{
		return $this->endLine;
	}
	
	public function write($line)
	{
		if (null === $this->logFile) {
			throw new BusinessLogicException(self::DEFINE_LOG_FILE_FIRST);
		}
		
		$file = fopen($this->logFile, 'a');
		fwrite($file, strftime($this->startLine) . $line . strftime($this->endLine));
		fclose($file);
		
		return $this;
	}
	
}

?>
