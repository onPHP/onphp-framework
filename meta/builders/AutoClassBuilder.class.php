<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Builders
	**/
	final class AutoClassBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			$out .= "abstract class Auto{$class->getName()}";
			
			$isNamed = false;
			
			if ($parent = $class->getParent())
				$out .= " extends {$parent->getName()}";
			elseif (
				$class->getPattern() instanceof DictionaryClassPattern
				&& $class->hasProperty('name')
			) {
				$out .= " extends NamedObject";
				$isNamed = true;
			} else
				$out .= " extends IdentifiableObject";
			
			if ($interfaces = $class->getInterfaces())
				$out .= ' implements '.implode(', ', $interfaces);
			
			$out .= "\n{\n";
			
			foreach ($class->getProperties() as $property) {
				
				if ($isNamed && $property->getName() == 'name')
					continue;
				
				if ($property->getName() == 'id' && !$parent)
					continue;
				
				$out .=
					"protected \${$property->getName()} = "
					."{$property->getType()->getDeclaration()};\n";
			}
			
			foreach ($class->getProperties() as $property) {
				
				if ($isNamed && $property->getName() == 'name')
					continue;
				
				if ($property->getName() == 'id' && !$parent)
					continue;
				
				if (
					!$property->getRelation()
					|| (
						$property->getRelation()->getId()
						== MetaRelation::ONE_TO_ONE
					)
				) {
					$out .= $property->toMethods($class);
				} else { // OneToMany || ManyToMany
					$name = $property->getName();
					$methodName = ucfirst($name);
					$remoteName = ucfirst($property->getName());
					
					$containerName = $class->getName().$remoteName.'DAO';
					
					$out .= <<<EOT

/**
 * @return {$containerName}
**/
public function get{$methodName}(\$lazy = false)
{
	if (!\$this->{$name}) {
		\$this->{$name} = new {$containerName}(\$this, \$lazy);
		
		if (\$this->id) {
			\$this->{$name}->fetch();
		}
	}
	
	return \$this->{$name};
}

EOT;
				}
			}
			
			$out .= "}\n";
			$out .= self::getHeel();
			
			return $out;
		}
	}
?>