<?php
/***************************************************************************
 *   Copyright (C) 2007 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Builders
	**/
	final class DTOClassBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			if (
				$class->getType()
				&&
					$class->getType()->getId()
					== MetaClassType::CLASS_ABSTRACT
			)
				$abstract = "abstract ";
			else
				$abstract = null;
				
			$out .= $abstract."class AutoDto{$class->getName()}";
			
			if ($parent = $class->getParent())
				$out .= " extends AutoDto{$parent->getName()}";
			
			$out .= "\n{\n";
			
			foreach ($class->getProperties() as $property) {
				if (!self::doPropertyBuild($property))
					continue;
				
				$out .=
					"protected \${$property->getName()} = "
					.
					(
						(
							$property->getRelation()
							&&
								$property->getRelation()->getId()
								== MetaRelation::ONE_TO_MANY
						)
							? "array();\n"
							: "{$property->getType()->getDeclaration()};\n"
					);
				
				if ($property->getFetchStrategyId() == FetchStrategy::LAZY) {
					$out .=
						"protected \${$property->getName()}Id = null;\n";
				}
			}
			
			if (!$abstract) {
				$out .= <<<EOT

	/**
	 * @return AutoDto{$class->getName()}
	**/
	public static function create()
	{
		return new self;
	}
	
EOT;
			}
			
			foreach ($class->getProperties() as $property) {
				if (!self::doPropertyBuild($property))
					continue;
				
				$out .= $property->toMethods($class);
			}
			
			$out .= "}\n";
			return $out.self::getHeel();
		}
		
		private static function doPropertyBuild(MetaClassProperty $property)
		{
			// do not redefine parent's properties
			if (
				$property->getClass()->getParent()
				&& array_key_exists(
					$property->getName(),
					$property->getClass()->getParentsProperties()
				)
			)
				return false;
			
			return true;
		}
	}
?>