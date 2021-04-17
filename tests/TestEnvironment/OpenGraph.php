<?php

namespace OnPHP\Tests\TestEnvironment;

class OpenGraph extends \OnPHP\Main\Markup\OGP\OpenGraph
{
	/**
	 * @return array
	 * @throws \OnPHP\Core\Exception\WrongArgumentException
	 */
	public function getList(): array
	{
		return parent::getList();
	}
}