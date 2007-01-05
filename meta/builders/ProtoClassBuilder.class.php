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
	final class ProtoClassBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			if ($type = $class->getType())
				$type = $type->toString().' ';
			else
				$type = null;

			$parent = $class->getParent();
			
			$classDump = self::dumpMetaClass($class);
			
			if ($parent) {
				$out .= <<<EOT
{$type}class Proto{$class->getName()} extends Proto{$parent->getName()}
{
{$classDump}
	/**
	 * @return Form
	**/
	public function makeForm()
	{
		\$form =
			parent::makeForm()->
			add(
EOT;
			} else {
				$out .= <<<EOT
{$type}class Proto{$class->getName()} extends AbstractProtoClass
{
{$classDump}
	public function makeForm()
	{
		/**
		 * @return Form
		**/
		return
			Form::create()->
			add(
EOT;
			}
			
			// sort out for wise and common defaults
			$prms = array();
			
			foreach ($class->getProperties() as $property) {
				
				if (
					(
						$property->getType() instanceof ObjectType
						&& !$property->getType()->isGeneric()
					)
					|| $property->isIdentifier()
				) {
					
					if (
						!$property->isIdentifier() 
						&& (
							$property->getType()->getClass()->getPattern()
								instanceof EnumerationClassPattern
						)
					)
						$isEnum = true;
					else
						$isEnum = false;

					if ($isEnum) {
						$className = $property->getType()->getClassName();
						
						$primitiveName = $property->getName()/*.'Id'*/;
					} elseif ($property->isIdentifier()) {
						$className = $class->getName();
						$primitiveName = 'id';
					} else {
						$className = $property->getType()->getClassName();
						$primitiveName = $property->getName()/*.'Id'*/;
					}
					
					if ($isEnum) {
						$primitive =
							"\nPrimitive::enumeration('{$primitiveName}')->\n"
							."of('{$className}')->\n";
					} else {
						if (
							!$property->getRelation()
							|| (
								$property->getRelationId()
									== MetaRelation::ONE_TO_ONE
								|| $property->getRelationId()
									== MetaRelation::LAZY_ONE_TO_ONE
							)
						) {
							if (
								!$property->getType()->isGeneric()
								&& $property->getType() instanceof ObjectType
								&& (
									$property->getType()->getClass()->getPattern()
										instanceof ValueObjectPattern
								)
							) {
								$primitive = array();
								$remote = $property->getType()->getClass();
								
								foreach ($remote->getProperties() as $remoteProperty) {
									$primitive[] = $remoteProperty->toPrimitive();
								}
							} else {
								$primitive =
									"\nPrimitive::identifier('{$primitiveName}')->\n";
								
								// should be specified only in childs
								if (
									!(
										$class->getType()
										&& (
											$class->getTypeId()
											== MetaClassType::CLASS_ABSTRACT
										)
										&& $property->isIdentifier()
									)
								) {
									$primitive .= "of('{$className}')->\n";
								}
							}
						} else {
							$primitive = null;
						}
					}
					
					if ($primitive && !is_array($primitive)) {
						if ($property->getType()->hasDefault())
							$primitive .=
								"setDefault({$property->getType()->getDefault()})->\n";
						
						if ($property->isRequired())
							$primitive .= "required()\n";
						else
							$primitive .= "optional()\n";
						
					}
				} else
					$primitive = $property->toPrimitive();
				
				if ($primitive) {
					if (is_array($primitive))
						$prms = array_merge($prms, $primitive);
					else
						$prms[] = $primitive;
				}
			}
			
			$out .= implode(")->\nadd(", $prms).");";
			
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
			
			$out .= <<<EOT

	}
}

EOT;

			return $out.self::getHeel();
		}
		
		private static function dumpMetaClass(MetaClass $class)
		{
			// it must be an evil bug, if there are any newlines anyway.
			$serialized = str_replace(chr(0), chr(9), serialize($class));
			
			$out = <<<EOT
	/**
	 * @return MetaClass
	**/
	public function getPropertyList()
	{
		static \$list = null;
		
		if (!\$list) {

EOT;

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
						$out .=
							"\$list['{$remoteProperty->getName()}'] = "
							.$remoteProperty->toLightProperty()->toString()
							."\n";
					}
				} else {
					$out .=
						"\$list['{$property->getName()}'] = "
						.$property->toLightProperty()->toString()
						."\n";
				}
			}
			
			$out .= <<<EOT
		}
		
		return \$list;
	}

EOT;
			return $out;
		}
	}
?>