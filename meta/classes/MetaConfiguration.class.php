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
	 * @ingroup MetaBase
	**/
	final class MetaConfiguration extends Singleton implements Instantiatable
	{
		private $out = null;
		
		private $classes = array();
		private $sources = array();
		
		private $defaultSource = null;
		
		/**
		 * @return MetaConfiguration
		**/
		public static function me()
		{
			return Singleton::getInstance('MetaConfiguration');
		}
		
		/**
		 * @return MetaOutput
		**/
		public static function out()
		{
			return self::me()->getOutput();
		}
		
		/**
		 * @return MetaConfiguration
		**/
		public function load($metafile)
		{
			$xml = simplexml_load_file($metafile);
			
			$liaisons = array();
			$references = array();
			
			// populate sources (if any)
			if (isset($xml->sources[0])) {
				foreach ($xml->sources[0] as $source) {
					$this->addSource($source);
				}
			}
			
			foreach ($xml->classes[0] as $xmlClass) {
				
				$class = new MetaClass((string) $xmlClass['name']);
				
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
							
							if (
								$property->getRelationId()
									== MetaRelation::LAZY_ONE_TO_ONE
								&& $property->getType()->isGeneric()
							) {
								throw new WrongArgumentException(
									'lazy one-to-one is supported only for '
									.'non-generic object types '
									.'('.$property->getName()
									.' @ '.$class->getName()
									.')'
								);
							}
							
							if (
								(
									(
										$property->getRelationId()
											== MetaRelation::ONE_TO_ONE
									) || (
										$property->getRelationId()
											== MetaRelation::LAZY_ONE_TO_ONE
									)
								) && (
									$property->getType()->getClassName()
									<> $class->getName()
								)
							) {
								$references[$property->getType()->getClassName()][]
									= $class->getName();
							}
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
			
			foreach ($references as $className => $list) {
				foreach ($list as $refer) {
					$this->classes[$className]->setReferencingClass($refer);
				}
			}
			
			// final sanity checking
			foreach ($this->classes as $name => $class) {
				$this->checkSanity($class);
			}
			
			return $this;
		}
		
		/**
		 * @return MetaConfiguration
		**/
		public function build()
		{
			$out = $this->getOutput();
			
			$out->
				infoLine('Building classes:');
			
			foreach ($this->classes as $name => $class) {
				$out->infoLine("\t".$class->getName().':');
				$class->dump();
				$out->newLine();
			}
			
			$out->
				newLine()->
				infoLine('Building DB schema:');
			
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
			
			$out->
				newLine()->
				infoLine('Suggested DB-schema changes: ');
			
			require ONPHP_META_AUTO_DIR.'schema.php';
			
			foreach ($this->classes as $name => $class) {
				if (
					$class->getTypeId() == MetaClassType::CLASS_ABSTRACT
					|| $class->getPattern() instanceof EnumerationClassPattern
				)
					continue;
				
				try {
					$target = $schema->getTableByName($class->getDumbName());
				} catch (MissingElementException $e) {
					// dropped or tableless
					continue;
				}
				
				try {
					$db = DBPool::me()->getLink($class->getSourceLink());
				} catch (BaseException $e) {
					$out->
						errorLine(
							'Can not connect using source link in \''
							.$class->getName().'\' class, skipping this step.');
					
					break;
				}
				
				try {
					$source = $db->getTableInfo($class->getDumbName());
				} catch (UnsupportedMethodException $e) {
					$out->
						errorLine(
							get_class($db)
							.' does not support tables introspection yet.',
							
							true
						);
					
					break;
				} catch (ObjectNotFoundException $e) {
					$out->errorLine(
						"table '{$class->getDumbName()}' not found, skipping."
					);
					continue;
				}
				
				$diff = DBTable::findDifferences(
					$db->getDialect(),
					$source,
					$target
				);
				
				if ($diff) {
					foreach ($diff as $line)
						$out->warningLine($line);
					
					$out->newLine();
				}
			}
			
			return $this;
		}
		
		/**
		 * @throws MissingElementException
		 * @return MetaClass
		**/
		public function getClassByName($name)
		{
			if (isset($this->classes[$name]))
				return $this->classes[$name];
			
			throw new MissingElementException(
				"knows nothing about '{$name}' class"
			);
		}
		
		/**
		 * @return MetaConfiguration
		**/
		public function setOutput(MetaOutput $out)
		{
			$this->out = $out;
			
			return $this;
		}
		
		/**
		 * @return MetaOutput
		**/
		public function getOutput()
		{
			return $this->out;
		}
		
		/**
		 * @return MetaConfiguration
		**/
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
		
		/**
		 * @return MetaClassProperty
		**/
		private function buildProperty($name, $type)
		{
			if (is_readable(ONPHP_META_TYPES.$type.'Type'.EXT_CLASS))
				$class = $type.'Type';
			else
				$class = 'ObjectType';
			
			return new MetaClassProperty($name, new $class($type));
		}
		
		/**
		 * @throws MissingElementException
		 * @return GenerationPattern
		**/
		private function guessPattern($name)
		{
			$class = $name.'Pattern';
			
			if (is_readable(ONPHP_META_PATTERNS.$class.EXT_CLASS))
				return Singleton::getInstance($class);
			
			throw new MissingElementException(
				"unknown pattern '{$name}'"
			);
		}
		
		/**
		 * @return MetaConfiguration
		**/
		private function checkSanity(MetaClass $class)
		{
			if (!$class->getParent()) {
				if (!$class->getPattern() instanceof ValueObjectPattern)
					Assert::isTrue(
						$class->getIdentifier() !== null,
						
						'only value objects can live without identifiers. '
						.'do not use them anyway'
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
			
			foreach ($class->getProperties() as $property) {
				if (
					!$property->getType()->isGeneric()
					&& $property->getType() instanceof ObjectType
					&&
						$property->getType()->getClass()->getPattern()
							instanceof ValueObjectPattern
				) {
					Assert::isTrue(
						$property->isRequired(),
						'optional value object is not supported'
					);
					
					Assert::isTrue(
						$property->getRelationId() == MetaRelation::ONE_TO_ONE,
						'value objects must have OneToOne relation'
					);
				}
			}
			
			return $this;
		}
	}
?>