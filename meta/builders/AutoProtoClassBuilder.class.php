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
			
			if ($parent) {
				$out .= <<<EOT
abstract class AutoProto{$class->getName()} extends Proto{$parent->getName()}
{
	/**
	 * @return Form
	**/
	public function makeForm()
	{
		\$form =
			parent::makeForm()
EOT;
				$redefined = array();
				
				// checking for redefined properties
				foreach ($class->getParentsProperties() as $property) {
					if ($class->hasProperty($property->getName())) {
						$redefined[] =
							"/* {$property->getClass()->getName()} */ "
							."drop('{$property->getName()}')";
					}
				}
				
				if ($redefined)
					$out .= "->\n".implode("->\n", $redefined);
			} else {
				$out .= <<<EOT
abstract class AutoProto{$class->getName()} extends AbstractProtoClass
{
	/**
	 * @return Form
	**/
	public function makeForm()
	{
		return
			Form::create()
EOT;
			}
			
			// sort out for wise and common defaults
			$prms = array();
			
			foreach ($class->getProperties() as $property) {
				if ($primitive = $property->toPrimitive($class)) {
					if (is_array($primitive))
						$prms = array_merge($prms, $primitive);
					else
						$prms[] = $primitive;
				}
			}
			
			if (count($prms)) {
				$out .= "->\nadd(".implode(")->\nadd(", $prms).");";
			} else {
				$out .= ";";
			}
			
			// parent's identificator should be concretized in childs
			if ($parent) {
				if ($parent->getIdentifier()) {
					$out .=
						"\n\n"
						."\$form->\nget('{$parent->getIdentifier()->getName()}')->"
						."of('{$class->getName()}');\n\n";
				}
				
				$out .= "return \$form;";
			}
			
			$classDump = self::dumpMetaClass($class);
			
			$out .= <<<EOT

	}

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

			if ($class->getParent()) {
				$out .= <<<EOT
		return
			array_merge(
				parent::makePropertyList(),
				array(
EOT;
				if ($class->getProperties())
					$out .= "\n";
			} else {
				$out .= <<<EOT
		return array(

EOT;
			}

			$list = array();
			
			foreach ($class->getProperties() as $property) {
				if (
					!$property->getType()->isGeneric()
					&& $property->getType() instanceof ObjectType
					&& (
						$property->getType()->getClass()->getPattern()
							instanceof ValueObjectPattern
					)
				) {
					$remote = $property->getType()->getClass();
					
					foreach ($remote->getProperties() as $remoteProperty) {
						$list[] =
							"'{$remoteProperty->getName()}' => "
							.$remoteProperty->toLightProperty()->toString();
					}
				} else {
					$list[] =
						"'{$property->getName()}' => "
						.$property->toLightProperty()->toString();
				}
			}
			
			$out .= implode(",\n", $list);
			
			if ($class->getParent()) {
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