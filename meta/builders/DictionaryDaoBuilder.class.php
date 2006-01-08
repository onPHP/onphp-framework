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

	final class DictionaryDaoBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			$out .= <<<EOT
	abstract class Auto{$class->getName()}DAO extends MappedStorableDAO
	{
		protected \$mapping = array(

EOT;

			$tabs = "\t\t\t";
			
			$mapping = array();
						
			foreach ($class->getProperties() as $property) {
				
				$row = $tabs;
				
				if ($property->getName() == $property->getDumbName())
					$map = 'null';
				else
					$map = $property->getDumbName();
				
				$row .= "'{$property->getName()}' => '{$map}'";
				
				$mapping[] = $row;
			}
			
			$out .= implode(",\n", $mapping);
			
			$className = $class->getName();
			$varName = strtolower($className[0]).substr($className, 1);
			
			$out .= <<<EOT

		);
		
		public function getTable()
		{
			return '{$class->getDumbName()}';
		}
		
		public function getObjectName()
		{
			return '{$class->getName()}';
		}
		
		public function getSequence()
		{
			return '{$class->getDumbName()}_id';
		}
		
		public function setQueryFields(InsertOrUpdateQuery \$query, {$className} \${$varName})
		{
			return
				\$query->
					// TODO
		}
		
		public function makeObject(&\$array, \$prefix = null)
		{
			return
				{$className}::create()->
				// TODO
		}
EOT;
			
			// bah.
			
			$out .= "\t}\n";
			$out .= self::getHeel();
			
			return $out;
		}
	}
?>