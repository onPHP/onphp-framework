<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
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
			
			$out .= <<<EOT
final class Proto{$class->getName()} extends AbstractProtoClass
{
	private \$form = null;

	protected function __construct()
	{
		\$this->form =
			Form::create()->
			add(
EOT;

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
						&&
							MetaConfiguration::me()->getClassByName(
								$property->getType()->getClass()
							)->
							getPattern() instanceof EnumerationClassPattern
					)
						$isEnum = true;
					else
						$isEnum = false;

					if ($isEnum) {
						$className = MetaConfiguration::me()->getClassByName(
							$property->getType()->getClass()
						)->getName();
						
						$primitiveName = $property->getName()/*.'Id'*/;
					} elseif ($property->isIdentifier()) {
						$className = $class->getName();
						$primitiveName = 'id';
					} else {
						$className = $property->getType()->getClass();
						$primitiveName = $property->getName()/*.'Id'*/;
					}
					
					if ($isEnum) {
						// FIXME: any better way to get enum's list?
						$primitive =
							"\nPrimitive::choice('{$primitiveName}')->\n"
							."setList(\n"
							."ArrayUtils::convertObjectList(\n"
							."{$className}::getList(new {$className}(1))\n"
							.")\n"
							.")->\n";
					} else {
						// FIXME: handle different relation types here
						$primitive =
							"\nPrimitive::identifier('{$primitiveName}')->\n"
							."of('{$className}')->\n";
					}
					
					if ($property->isRequired())
						$primitive .= "required()\n";
					else
						$primitive .= "optional()\n";
					
				} else
					$primitive = $property->toPrimitive();
				
				$prms[] = $primitive;
			}
			
			$out .= implode(")->\nadd(", $prms).");";
			
			$out .= <<<EOT

	}
	
	public function getForm()
	{
		return \$this->form;
	}
}

EOT;

			return $out.self::getHeel();
		}
	}
?>