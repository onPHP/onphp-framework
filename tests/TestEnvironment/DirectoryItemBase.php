<?php

namespace OnPHP\Tests\TestEnvironment;

use OnPHP\Core\Base\Identifiable;

abstract class DirectoryItemBase implements Identifiable
{
	protected $textField;
	protected $fileName;
	protected $inner;
	protected $id;

	public function setId($id)
	{
		$this->id = $id;

		return $this;
	}

	public function getId()
	{
		return $this->id;
	}

	public function setTextField($textField)
	{
		$this->textField = $textField;

		return $this;
	}

	public function getTextField()
	{
		return $this->textField;
	}

	public function setFileName($fileName)
	{
		$this->fileName = $fileName;

		return $this;
	}

	public function getFileName()
	{
		return $this->fileName;
	}

	public function setInner(DirectoryItem $inner)
	{
		$this->inner = $inner;

		return $this;
	}

	public function dropInner()
	{
		$this->inner = null;

		return $this;
	}

	public function getInner()
	{
		return $this->inner;
	}
}
?>