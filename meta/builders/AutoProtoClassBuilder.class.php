<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Builders
	**/
	final class AutoProtoClassBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			$parent = $class->getParent();
			
			if ($class->hasBuildableParent())
				$parentName = 'Proto'.$parent->getName();
			else
				$parentName = 'AbstractProtoClass';
			
			$out .= <<<EOT
abstract class AutoProto{$class->getName()} extends {$parentName}
{
EOT;
			$classDump = self::dumpMetaClass($class);
			
			$out .= <<<EOT

{$classDump}
}

EOT;

			return $out.self::getHeel();
		}
		
		private static function dumpMetaClass(MetaClass $class)
		{
			$out = <<<EOT
	protected function makePropertyList()
	{

EOT;

			if ($class->hasBuildableParent()) {
				$out .= <<<EOT
		return
			array_merge(
				parent::makePropertyList(),
				array(
EOT;
				if ($class->getWithInternalProperties())
					$out .= "\n";
			} else {
				$out .= <<<EOT
		return array(

EOT;
			}

			$list = array();
			
			foreach ($class->getWithInternalProperties() as $property) {
				if (
					!$property->getType()->isGeneric()
					&& $property->getType() instanceof ObjectType
					&& (
						$property->getType()->getClass()->getPattern()
							instanceof ValueObjectPattern
					)
				) {
					$remote = $property->getType()->getClass();
					
					$composite = array();
					
					foreach ($remote->getProperties() as $remoteProperty) {
						$composite[] = "add(\n".$remoteProperty->toLightProperty($remote)->toString()."\n)";
					}
					
					$list[] =
						"'{$property->getName()}' =>\n"
						."CompositeLightMetaProperty::create('{$remote->getName()}', '{$property->getName()}')->\n"
						.implode("->\n", $composite);
				} else {
					$list[] =
						"'{$property->getName()}' => "
						.$property->toLightProperty($class)->toString();
				}
			}
			
			$out .= implode(",\n", $list);
			
			if ($class->hasBuildableParent()) {
				$out .= "\n)";
			}
			
			$out .= <<<EOT

		);
	}
EOT;
			return $out;
		}
	}
?>