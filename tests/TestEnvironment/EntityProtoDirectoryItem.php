<?php

namespace OnPHP\Tests\TestEnvironment;

use OnPHP\Core\Form\Primitive;
use OnPHP\Main\EntityProto\EntityProto;

final class EntityProtoDirectoryItem extends EntityProto
{
	public function className()
	{
		return DirectoryItem::class;
	}

	public function getFormMapping()
	{
		return array(
			'items' => Primitive::formsList('items')->
				ofProto(new EntityProtoDirectoryItem)->
				required(),

			'textField' => Primitive::string('textField')->
				setMax(256)->
				optional(),

			'fileName' => Primitive::file('contents')->
				required(),

			'inner' => Primitive::form('inner')->
				ofProto(new EntityProtoDirectoryItem)->
				optional(),
		);
	}
}
?>