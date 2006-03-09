<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class ProtoClassBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			$out .= <<<EOT
final class Proto{$class->getName()} extends Singletone
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
					$property->getType() instanceof ObjectType
					&& !$property->getType()->isGeneric()
				) {
					$primitive =
						"\nPrimitive::identifier('{$property->getName()}Id')->\n"
						."of('{$property->getType()->getClass()}')\n";
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
	
	public function importForm()
	{
		\$form = &\$this->form;


EOT;

			$get = $post = array();
			
			foreach ($class->getProperties() as $property) {
				
				if (
					$property->getType() instanceof ObjectType
					&& !$property->getType()->isGeneric()
				) {
					$name = "{$property->getName()}Id";
				} else
					$name = $property->getName();
				
				if ($property->isIdentifier())
					$get[] = $name;
				else
					$post[] = $name;
			}
			
			if ($get) {
				$out .= "// GET\n";
				
				foreach ($get as $name) {
					$out .= "\$form->importOne('{$name}', \$_GET);\n";
				}
			}
			
			if ($get) {
				$out .= "\n// POST\n";
				
				foreach ($post as $name) {
					$out .= "\$form->importOne('{$name}', \$_POST);\n";
				}
			}
			
			$out .= <<<EOT

		return \$this;
	}
}

EOT;
			
			
			
			return $out.self::getHeel();
		}
	}
?>