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
	 * @ingroup MetaBase
	**/
	final class MetaConfiguration extends Singleton implements Instantiatable
	{
		private $classes = array();
		private $sources = array();
		
		private $defaultSource = null;
		
		public static function me()
		{
			return Singleton::getInstance('MetaConfiguration');
		}
		
		public function load($metafile)
		{
			$xml = simplexml_load_file($metafile);
			
			$liaisons = array();
			
			// populate sources (if any)
			if (isset($xml->sources[0])) {
				foreach ($xml->sources[0] as $source) {
					$this->addSource($source);
				}
			}
			
			foreach ($xml->classes[0] as $xmlClass) {
				$name = (string) $xmlClass['name'];
				
				Assert::isFalse(
					isset($this->classes[$name]),
					'class name collision found for '.$name
				);
				
				$class = new MetaClass($name);
				
				if (isset($xmlClass['source'])) {
					
					$source = (string) $xmlClass['source'];
					
					Assert::isTrue(
						isset($this->sources[$source]),
						"unknown source '{$source}' specified "
						."for class '{$class->getName()}'"
					);
					
					$class->setSourceLink($source);
				} elseif ($this->defaultSource) {
					$class->setSourceLink($this->defaultSource);
				}
				
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

				if (isset($xmlClass->properties[0]->identifier)) {
					
					$id = $xmlClass->properties[0]->identifier;
					
					if (!isset($id['name']))
						$name = 'id';
					else
						$name = (string) $id['name'];
					
					if (!isset($id['type']))
						$type = 'BigInteger';
					else
						$type = (string) $id['type'];
					
					$property = $this->buildProperty($name, $type);
					
					if (isset($id['column'])) {
						$property->setColumnName(
							(string) $id['column']
						);
					}
					
					$property->
						setIdentifier(true)->
						required();
					
					$class->addProperty($property);
					
					unset($xmlClass->properties[0]->identifier);
				}
				
				// populate properties
				foreach ($xmlClass->properties[0] as $xmlProperty) {
					
					$property = $this->buildProperty(
						(string) $xmlProperty['name'],
						(string) $xmlProperty['type']
					);
					
					if (isset($xmlProperty['column'])) {
						$property->setColumnName(
							(string) $xmlProperty['column']
						);
					}
					
					if ((string) $xmlProperty['required'] == 'true')
						$property->required();
					
					if (isset($xmlProperty['identifier'])) {
						throw new WrongArgumentException(
							'obsoleted identifier description found in '
							."{$class->getName()} class;\n"
							.'you must use <identifier /> instead.'
						);
					}
					
					if (isset($xmlProperty['size']))
						$property->setSize((int) $xmlProperty['size']);
					
					if (!$property->getType()->isGeneric()) {
						
						if (!isset($xmlProperty['relation']))
							throw new MissingElementException(
								'relation should be set for non-generic '
								."property '{$property->getName()}' type '"
								.get_class($property->getType())."'"
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
					
					if (isset($xmlProperty['default']))
						// will be correctly autocasted further down the code
						$property->getType()->setDefault(
							(string) $xmlProperty['default']
						);
					
					$class->addProperty($property);
				}
				
				$class->setPattern(
					$this->guessPattern((string) $xmlClass->pattern['name'])
				);
				
				$this->classes[$class->getName()] = $class;
			}
			
			foreach ($liaisons as $class => $parent) {
				if (isset($this->classes[$parent])) {
					
					Assert::isFalse(
						$this->classes[$parent]->getTypeId()
						== MetaClassType::CLASS_FINAL,
						
						"'{$parent}' is final, thus can not have childs"
					);
					
					if (
						$this->classes[$class]->getPattern()
							instanceof DictionaryClassPattern
					)
						throw new UnsupportedMethodException(
							'DictionaryClass pattern does '
							.'not support inheritance'
						);
					
					$this->classes[$class]->setParent(
						$this->classes[$parent]
					);
				} else
					throw new MissingElementException(
						"unknown parent class '{$parent}'"
					);
			}
			
			// final sanity checking
			foreach ($this->classes as $name => $class) {
				$this->checkSanity($class);
			}
			
			return $this;
		}
		
		public function build()
		{
			foreach ($this->classes as $name => $class) {
				echo $name."\n";
				$class->dump();
			}
			
			$schema = SchemaBuilder::getHead();
			
			foreach ($this->classes as $name => $class) {
				$schema .= SchemaBuilder::build($class);
			}
			
			foreach ($this->classes as $name => $class) {
				$schema .= SchemaBuilder::buildRelations($class);
			}
			
			$schema .= '?>';
			
			BasePattern::dumpFile(
				ONPHP_META_AUTO_DIR.'schema.php',
				Format::indentize($schema)
			);
		}
		
		public function getClassByName($name)
		{
			if (isset($this->classes[$name]))
				return $this->classes[$name];
			
			throw new MissingElementException(
				"knows nothing about '{$name}' class"
			);
		}
		
		private function addSource(SimpleXMLElement $source)
		{
			$name = (string) $source['name'];
			
			$default =
				isset($source['default']) && (string) $source['default'] == 'true'
					? true
					: false;
			
			Assert::isFalse(
				isset($this->sources[$name]),
				"duplicate source - '{$name}'"
			);
			
			Assert::isFalse(
				$default && $this->defaultSource !== null,
				'too many default sources'
			);
			
			$this->sources[$name] = $default;
			
			if ($default)
				$this->defaultSource = $name;
			
			return $this;
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
			
			throw new MissingElementException(
				"unknown pattern '{$name}'"
			);
		}
		
		private function checkSanity(MetaClass $class)
		{
			if (!$class->getParent()) {
				Assert::isTrue(
					$class->getIdentifier() !== null,
					
					'no one can live without identifier'
				);
			} else {
				$parent = $class->getParent();
				
				while ($parent->getParent())
					$parent = $parent->getParent();
				
				Assert::isTrue(
					$parent->getIdentifier() !== null,
					
					'can not find parent with identifier'
				);
			}
			
			if (
				$class->getType()
				&& $class->getTypeId()
					== MetaClassType::CLASS_SPOOKED
			) {
				Assert::isFalse(
					count($class->getProperties()) > 1,
					'spooked classes must have only identifier'
				);
				
				Assert::isTrue(
					($class->getPattern() instanceof SpookedClassPattern
					|| $class->getPattern() instanceof SpookedEnumerationPattern),
					'spooked classes must use spooked patterns only'
				);
			}
			
			return $this;
		}
	}
?>