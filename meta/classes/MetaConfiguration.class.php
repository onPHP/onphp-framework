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
				
				if (isset($xmlClass['table']))
					$class->setTableName((string) $xmlClass['table']);
				
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
					
					$property = $this->makeProperty($name, $type, $class);
					
					if (isset($id['column'])) {
						$property->setColumnName(
							(string) $id['column']
						);
					} elseif (
						$property->getType() instanceof ObjectType
						&& !$property->getType()->isGeneric()
					) {
						$property->setColumnName($property->getConvertedName().'_id');
					} else {
						$property->setColumnName($property->getConvertedName());
					}
					
					$property->
						setIdentifier(true)->
						required();
					
					$class->addProperty($property);
					
					unset($xmlClass->properties[0]->identifier);
				}
				
				$class->setPattern(
					$this->guessPattern((string) $xmlClass->pattern['name'])
				);
				
				// populate properties
				foreach ($xmlClass->properties[0] as $xmlProperty) {
					
					$property = $this->makeProperty(
						(string) $xmlProperty['name'],
						(string) $xmlProperty['type'],
						$class
					);
					
					if (isset($xmlProperty['column'])) {
						$property->setColumnName(
							(string) $xmlProperty['column']
						);
					} elseif (
						$property->getType() instanceof ObjectType
						&& !$property->getType()->isGeneric()
					) {
						$property->setColumnName($property->getConvertedName().'_id');
					} else {
						$property->setColumnName($property->getConvertedName());
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
									$property->getRelationId()
										== MetaRelation::ONE_TO_ONE
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
					
					if (isset($xmlProperty['default'])) {
						// will be correctly autocasted further down the code
						$property->getType()->setDefault(
							(string) $xmlProperty['default']
						);
					}

					$class->addProperty($property);
				}
				
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
				$class = $this->getClassByName($className);
				
				if (
					(
						$class->getPattern() instanceof ValueObjectPattern
					) || (
						$class->getTypeId() == MetaClassType::CLASS_ABSTRACT
					)
				) {
					continue;
				}
				
				foreach ($list as $refer) {
					$remote = $this->getClassByName($refer);
					if (
						(
							$remote->getPattern() instanceof ValueObjectPattern
						) && (
							isset($references[$refer])
						)
					) {
						foreach ($references[$refer] as $holder) {
							$this->classes[$className]->
								setReferencingClass($holder);
						}
					} elseif ($remote->getTypeId() != MetaClassType::CLASS_ABSTRACT) {
						$this->classes[$className]->setReferencingClass($refer);
					}
				}
			}
			
			// final sanity checking
			foreach ($this->classes as $name => $class) {
				$this->checkSanity($class);
			}
			
			// check for recursion in relations and spooked properties
			foreach ($this->classes as $name => $class) {
				foreach ($class->getProperties() as $property) {
					if ($property->getRelationId() == MetaRelation::ONE_TO_ONE) {
						if (
							(
								$property->getClass()->getPattern()
									instanceof SpookedClassPattern
							) || (
								$property->getClass()->getPattern()
									instanceof SpookedEnumerationPattern
							)
						) {
							$property->setFetchStrategy(FetchStrategy::cascade());
						} else {
							$this->checkRecursion($property, $class);
						}
					}
				}
			}
			
			return $this;
		}
		
		/**
		 * @return MetaConfiguration
		**/
		public function build()
		{
			return $this->
				buildClasses()->
				buildSchema()->
				buildSchemaChanges()->
				checkForStaleFiles();
		}
		
		/**
		 * @return MetaConfiguration
		**/
		public function buildClasses()
		{
			$out = $this->getOutput();
			
			$out->
				infoLine('Building classes:');
			
			foreach ($this->classes as $name => $class) {
				$out->infoLine("\t".$name.':');
				$class->dump();
				$out->newLine();
			}
			
			return $this;
		}
		
		/**
		 * @return MetaConfiguration
		**/
		public function buildSchema()
		{
			$out = $this->getOutput();

			$out->
				newLine()->
				infoLine('Building DB schema:');
			
			$schema = SchemaBuilder::getHead();
			
			foreach ($this->classes as $class) {
				$schema .= SchemaBuilder::build($class);
			}
			
			foreach ($this->classes as $class) {
				$schema .= SchemaBuilder::buildRelations($class);
			}
			
			$schema .= '?>';
			
			BasePattern::dumpFile(
				ONPHP_META_AUTO_DIR.'schema.php',
				Format::indentize($schema)
			);

			return $this;
		}
		
		/**
		 * @return MetaConfiguration
		**/
		public function buildSchemaChanges()
		{
			$out = $this->getOutput();
			$out->
				newLine()->
				infoLine('Suggested DB-schema changes: ');
			
			require ONPHP_META_AUTO_DIR.'schema.php';
			
			foreach ($this->classes as $class) {
				if (
					$class->getTypeId() == MetaClassType::CLASS_ABSTRACT
					|| $class->getPattern() instanceof EnumerationClassPattern
				)
					continue;
				
				try {
					$target = $schema->getTableByName($class->getTableName());
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
							.$class->getName().'\' class, skipping this step.'
						);
					
					break;
				}
				
				try {
					$source = $db->getTableInfo($class->getTableName());
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
						"table '{$class->getTableName()}' not found, skipping."
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
		 * @return MetaConfiguration
		**/
		public function checkForStaleFiles()
		{
			$this->getOutput()->
				newLine()->
				infoLine('Stale files: ');
			
			return $this->
				checkDirectory(ONPHP_META_AUTO_BUSINESS_DIR, 'Auto', null)->
				checkDirectory(ONPHP_META_AUTO_DAO_DIR, 'Auto', 'DAO')->
				checkDirectory(ONPHP_META_AUTO_PROTO_DIR, 'AutoProto', null);
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
		
		public function getClassList()
		{
			return $this->classes;
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
		private function checkDirectory($directory, $preStrip, $postStrip)
		{
			foreach (
				glob($directory.'*.class.php', GLOB_NOSORT)
				as $filename
			) {
				$name =
					substr(
						basename($filename, $postStrip.EXT_CLASS),
						strlen($preStrip)
					);
				
				if (!isset($this->classes[$name]))
					$this->getOutput()->warningLine(
						"\t"
						.str_replace(
							getcwd().DIRECTORY_SEPARATOR,
							null,
							$filename
						)
					);
			}
			
			return $this;
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
		private function makeProperty($name, $type, MetaClass $class)
		{
			if (is_readable(ONPHP_META_TYPES.$type.'Type'.EXT_CLASS))
				$typeClass = $type.'Type';
			else
				$typeClass = 'ObjectType';

			return new MetaClassProperty($name, new $typeClass($type), $class);
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
		
		private function checkRecursion(
			MetaClassProperty $property,
			MetaClass $holder,
			$paths = array()
		) {
			Assert::isTrue(
				$property->getRelationId()
				== MetaRelation::ONE_TO_ONE
			);
			
			$remote = $property->getType()->getClass();
			
			if (isset($paths[$holder->getName()][$remote->getName()]))
				return true;
			else {
				$paths[$holder->getName()][$remote->getName()] = true;
				
				foreach ($remote->getProperties() as $remoteProperty) {
					if (
						$remoteProperty->getRelationId()
						== MetaRelation::ONE_TO_ONE
					) {
						if (
							$this->checkRecursion(
								$remoteProperty,
								$holder,
								$paths
							)
						) {
							$remoteProperty->setFetchStrategy(
								FetchStrategy::cascade()
							);
						}
					}
				}
			}
			
			return false;
		}
	}
?>