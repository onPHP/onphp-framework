<?php

namespace OnPHP\Tests\TestEnvironment\Main\Base;

use OnPHP\Core\Exception\WrongArgumentException;

class DateRange extends \OnPHP\Main\Base\DateRange
{
	/**
	 * @param $value
	 * @throws WrongArgumentException
	 */
	public function checkType($value): void
	{
		parent::checkType($value);
	}

	/**
	 * @return string
	 */
	public function getObjectName(): string
	{
		return parent::getObjectName();
	}
}