<?php

namespace OnPHP\Tests\TestEnvironment;

final class DirectoryItem extends DirectoryItemBase
{
	private $items = array();

	public function setItems($items)
	{
		$this->items = $items;

		return $this;
	}

	public function getItems()
	{
		return $this->items;
	}

	public static function create()
	{
		return new self;
	}
}
?>