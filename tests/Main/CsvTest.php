<?php
/***************************************************************************
 *   Copyright (C) 2008 by Michael V. Tchervyakov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Tests\Main;

use OnPHP\Main\Markup\Csv;
use OnPHP\Tests\TestEnvironment\TestCase;

/**
 * @group utils
 * @group csv
 */
final class CsvTest extends TestCase
{
	public function testRender()
	{
		$array = array(
			array(1, 2.2, -3, null),
			array("5\n5", "6'6", '7"7', '8,8')
		);

		$string =
			'1,2.2,-3,'.Csv::CRLF
			."\"5\n5\",6'6,\"7\"\"7\",\"8,8\"".Csv::CRLF;

		$csv = Csv::create()->setArray($array);
		$this->assertEquals($csv->render(), $string);
	}

	public function testContentHeader()
	{
		$csv = Csv::create(false);

		$this->assertEquals(
			$csv->getContentTypeHeader()->toString(),
			'text/csv; header="absent"'
		);

		$csv = Csv::create(true);

		$this->assertEquals(
			$csv->getContentTypeHeader()->toString(),
			'text/csv; header="present"'
		);
	}
}
?>