<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Util\Archiver;

use OnPHP\Core\Exception\UnsupportedMethodException;
use OnPHP\Core\Exception\WrongStateException;

/**
 * PECL ZipArchive proxy with Info-Zip wrapper.
 * 
 * @see http://pecl.php.net/package/zip
 *
 * @ingroup Utils
**/
final class InfoZipArchive extends FileArchive
{
	private $zipArchive = null;

	public function __construct($cmdBinPath = '/usr/bin/unzip')
	{
		$usingCmd = $cmdBinPath;

		if (class_exists('\ZipArchive', false)) {

			$this->zipArchive = new \ZipArchive();
			$usingCmd = null;

		} elseif ($usingCmd === null)
			throw
				new UnsupportedMethodException(
					'no built-in support for zip'
				);

		parent::__construct($usingCmd);
	}

	public function open($sourceFile)
	{
		parent::open($sourceFile);

		if ($this->zipArchive) {
			$resultCode = $this->zipArchive->open($sourceFile);

			if ($resultCode !== true)
				throw new ArchiverException(
					'ZipArchive::open() returns error code == '.$resultCode
				);
		}

		return $this;
	}

	public function readFile($fileName)
	{
		if (!$this->sourceFile)
			throw
				new WrongStateException(
					'dude, open an archive first.'
				);

		if ($this->zipArchive) {
			$result = $this->zipArchive->getFromName($fileName);

			if ($result === false)
				throw new ArchiverException(
					'ZipArchive::getFromName() failed'
				);

			return $result;
		}

		$options = '-c -q'
			.' '.escapeshellarg($this->sourceFile)
			.' '.escapeshellarg($fileName);

		return $this->execStdoutOptions($options);
	}
}
?>