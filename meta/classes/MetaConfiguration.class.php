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

	final class MetaConfiguration extends Singleton
	{
		private $classes = array();
		
		public static function me()
		{
			return Singleton::getInstance('MetaConfiguration');
		}
		
		public function load($metafile)
		{
			$xml = simplexml_load_file($metafile);
			
			$liaisons = array();
			
			foreach ($xml->classes[0] as $xmlClass) {
				
				$class = new MetaClass((string) $xmlClass['name']);
				
				if (isset($xmlClass['type']))
					$class->setType(
						new MetaClassType(
							(string) $xmlClass['type']
						)
					);
				
				// lazy existence checking
				if (isset($xmlClass['extends']))
					$liaisons[$class->getName()] = (string) $xmlClass['extends'];
				
				// populate implemented interfaces
				foreach ($xmlClass->implement as $xmlImplement)
					$class->addInterface((string) $xmlImplement['interface']);
				
				// populate properties
				foreach ($xmlClass->properties[0] as $xmlProperty) {
					
					$property = $this->buildProperty(
						(string) $xmlProperty['name'],
						(string) $xmlProperty['type']
					);
					
					if ((string) $xmlProperty['required'] == 'true')
						$property->required();
					
					if ((string) $xmlProperty['identifier'] == 'true')
						$property->setIdentifier(true);
					
					if (isset($xmlProperty['size']))
						$property->setSize((int) $xmlProperty['size']);
					
					if (!$property->getType()->isGeneric()) {
						
						if (!isset($xmlProperty['relation']))
							throw new WrongArgumentException(
								'relation should be set for non-generic '
								."type '".get_class($property->getType())."'"
								." of '{$class->getName()}' class"
							);
						else {
							$property->setRelation(
								new MetaRelation(
									(string) $xmlProperty['relation']
								)
							);
						}
					}
					
					$class->addProperty($property);
				}
				
				$class->setPattern(
					$this->guessPattern((string) $xmlClass->pattern['name'])
				);
				
				$this->classes[$class->getName()] = $class;
			}
			
			foreach ($liaisons as $class => $parent) {
				if (isset($this->classes[$parent])) {
					
					if (
						$this->classes[$class]->getPattern()
							instanceof DictionaryClassPattern
					)
						throw new WrongStateException(
							'DictionaryClass pattern doesn '
							.'not support inheritance'
						);
					
					$this->classes[$class]->setParent(
						$this->classes[$parent]
					);
				} else
					throw new ObjectNotFoundException(
						"unknown parent class '{$parent}'"
					);
			}
			
			return $this;
		}
		
		public function build()
		{
			foreach ($this->classes as $name => $class) {
				echo $name."\n";
				$class->dump();
			}
		}
		
		public function getClassByName($name)
		{
			if (isset($this->classes[$name]))
				return $this->classes[$name];
			
			throw new ObjectNotFoundException(
				"knows nothing about '{$name}' class"
			);
		}
		
		private function buildProperty($name, $type)
		{
			if (is_readable(ONPHP_META_TYPES.$type.'Type'.EXT_CLASS))
				$class = $type.'Type';
			else
				$class = 'ObjectType';
			
			return new MetaClassProperty($name, new $class($type));
		}
		
		private function guessPattern($name)
		{
			$class = $name.'Pattern';
			
			if (is_readable(ONPHP_META_PATTERNS.$class.EXT_CLASS))
				return Singleton::getInstance($class);
			
			throw new ObjectNotFoundException(
				"unknown pattern '{$name}'"
			);
		}
	}
?>