<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Meta\Entity;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Base\Enum;
use OnPHP\Core\Base\Enumeration;
use OnPHP\Core\Base\Instantiatable;
use OnPHP\Core\Base\Prototyped;
use OnPHP\Core\Base\Singleton;
use OnPHP\Core\DB\DBPool;
use OnPHP\Core\Exception\BaseException;
use OnPHP\Core\Exception\MissingElementException;
use OnPHP\Core\Exception\ObjectNotFoundException;
use OnPHP\Core\Exception\UnimplementedFeatureException;
use OnPHP\Core\Exception\UnsupportedMethodException;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Core\Exception\WrongStateException;
use OnPHP\Core\Form\Form;
use OnPHP\Core\Form\FormUtils;
use OnPHP\Core\Logic\Expression;
use OnPHP\Core\OSQL\DBTable;
use OnPHP\Core\OSQL\OSQL;
use OnPHP\Main\Criteria\Criteria;
use OnPHP\Main\Criteria\FetchStrategy;
use OnPHP\Main\DAO\DAOConnected;
use OnPHP\Main\Util\ClassUtils;
use OnPHP\Meta\Builder\ContainerClassBuilder;
use OnPHP\Meta\Builder\SchemaBuilder;
use OnPHP\Meta\Console\Format;
use OnPHP\Meta\Console\MetaOutput;
use OnPHP\Meta\Pattern\AbstractClassPattern;
use OnPHP\Meta\Pattern\BasePattern;
use OnPHP\Meta\Pattern\DictionaryClassPattern;
use OnPHP\Meta\Pattern\EnumClassPattern;
use OnPHP\Meta\Pattern\EnumerationClassPattern;
use OnPHP\Meta\Pattern\GenerationPattern;
use OnPHP\Meta\Pattern\InternalClassPattern;
use OnPHP\Meta\Pattern\SpookedClassPattern;
use OnPHP\Meta\Pattern\SpookedEnumPattern;
use OnPHP\Meta\Pattern\SpookedEnumerationPattern;
use OnPHP\Meta\Pattern\ValueObjectPattern;
use OnPHP\Meta\Type\FixedLengthStringType;
use OnPHP\Meta\Type\HttpUrlType;
use OnPHP\Meta\Type\NumericType;
use OnPHP\Meta\Type\ObjectType;
use OnPHP\Meta\Util\MetaClassPull;
use OnPHP\Meta\Util\NamespaceUtils;
use OnPHP\Meta\Util\UsesPull;
use ReflectionProperty;
use SimpleXMLElement;

/**
 * @ingroup MetaBase
**/
final class MetaConfiguration extends Singleton implements Instantiatable
{
	private $out = null;
	
	private $sources = array();
	private $namespaces = array();
	
	private $liaisons = array();
	private $references = array();
	
	private $defaultSource = null;
	
	private $forcedGeneration	= false;
	private $dryRun				= false;
	
	private $checkEnumerationRefIntegrity = false;
	
	/**
	 * @return MetaConfiguration
	**/
	public static function me()
	{
		return Singleton::getInstance(self::class);
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
	public function setForcedGeneration($orly)
	{
		$this->forcedGeneration = $orly;
		
		return $this;
	}
	
	public function isForcedGeneration()
	{
		return $this->forcedGeneration;
	}
	
	/**
	 * @return MetaConfiguration
	**/
	public function setDryRun($dry)
	{
		$this->dryRun = $dry;
		
		return $this;
	}
	
	public function isDryRun()
	{
		return $this->dryRun;
	}
	
	/**
	 * @return MetaConfiguration
	**/
	public function setWithEnumerationRefIntegrityCheck($orly)
	{
		$this->checkEnumerationRefIntegrity = $orly;
		
		return $this;
	}
	
	/**
	 * @return MetaConfiguration
	**/
	public function load($metafile, $generate = true)
	{
		$this->loadXml($metafile, $generate);
		
		// check sources
		foreach (MetaClassPull::me()->getList() as $name => $class) {
			$sourceLink = $class->getSourceLink();
			
			if (isset($sourceLink)) {
				Assert::isTrue(
					isset($this->sources[$sourceLink]),
					"unknown source '{$sourceLink}' specified "
					."for class '{$name}'"
				);
			} elseif ($this->defaultSource) {
				$class->setSourceLink($this->defaultSource);
			}
		}
		
		foreach ($this->liaisons as $className => $parentClassName) {
			if ($parent = MetaClassPull::me()->getClass($parentClassName)) {
				
				Assert::isFalse(
					$parent->getTypeId()
						== MetaClassType::CLASS_FINAL,
					
					"'{$parentClassName}' is final, thus can not have childs"
				);
				
				$class = MetaClassPull::me()->getClass($className);
				
				if (
					$class->getPattern()
						instanceof DictionaryClassPattern
				) {
					throw new UnsupportedMethodException(
						'DictionaryClass pattern does '
						.'not support inheritance'
					);
				}
				
				$class->setParent($parent);
			} else {
				throw new MissingElementException(
					"unknown parent class '{$parentClassName}'"
				);
			}
		}
		
		// search for referencing classes
		foreach ($this->references as $className => $list) {
			$class = MetaClassPull::me()->getClass($className);
			
			if (
				($class->getPattern() instanceof ValueObjectPattern) 
				|| ($class->getPattern() instanceof InternalClassPattern)
				|| ($class->getPattern() instanceof AbstractClassPattern)
			) {
				continue;
			}
			
			foreach ($list as $refer) {
				$remote = MetaClassPull::me()->getClass($refer);
				
				if (
					$remote->getPattern() instanceof ValueObjectPattern
					&& isset($this->references[$refer])
				) {
					foreach ($this->references[$refer] as $holder) {
						$holderClass = MetaClassPull::me()->getClass($refer);
						$class->setReferencingClass($holderClass);
					}
				} elseif (
					(!$remote->getPattern() instanceof AbstractClassPattern)
					&& (!$remote->getPattern() instanceof InternalClassPattern)
					&& ($remote->getTypeId() <> MetaClassType::CLASS_ABSTRACT)
				) {
					$referClass = MetaClassPull::me()->getClass($refer);
					$class->setReferencingClass($referClass);
				}
			}
		}
		
		// final sanity checking
		foreach (MetaClassPull::me()->getList() as $name => $class) {
			$this->checkSanity($class);
		}
		
		// check for recursion in relations and spooked properties
		foreach (MetaClassPull::me()->getList() as $name => $class) {
			foreach ($class->getProperties() as $property) {
				if ($property->getRelationId() == MetaRelation::ONE_TO_ONE) {
					$pattern = $property->getType()->getClass()->getPattern();
					
					if (
						(
							$pattern instanceof SpookedClassPattern
							|| $pattern instanceof SpookedEnumerationPattern
							|| $pattern instanceof SpookedEnumPattern
						) && (
							$property->getFetchStrategy()
							&& (
								$property->getFetchStrategy()->getId()
								!= FetchStrategy::LAZY
							)
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
	public function buildClasses()
	{
		$out = $this->getOutput();
		
		$out->
			infoLine('Building classes:');

		foreach (MetaClassPull::me()->getList() as $name => $class) {
			if (!$class->doBuild() || $class->isInternal()) {
				continue;
			} else {
				$out->infoLine("\t".$name.':');
			}
			
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
		
		$tables = array();
		
		foreach (MetaClassPull::me()->getList() as $class) {
			if (
				(!$class->getParent() && !count($class->getProperties()))
				|| (!$class->getPattern()->tableExists())
			) {
				continue;
			}
			
			foreach ($class->getAllProperties() as $property)
				$tables[
					$class->getTableName()
				][
					// just to sort out dupes, if any
					$property->getColumnName()
				] = $property;
		}
		
		foreach ($tables as $name => $propertyList) {
			if ($propertyList) {
				$schema .= SchemaBuilder::buildTable($name, $propertyList);
			}
		}
		
		foreach (MetaClassPull::me()->getList() as $class) {
			if (!$class->getPattern()->tableExists()) {
				continue;
			}
			
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
		
		foreach (MetaClassPull::me()->getList() as $class) {
			if (
				$class->getTypeId() == MetaClassType::CLASS_ABSTRACT
				|| $class->getPattern() instanceof EnumerationClassPattern
				|| $class->getPattern() instanceof EnumClassPattern

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
	public function buildContainers()
	{
		$force = $this->isForcedGeneration();
		
		$out = $this->getOutput();
		$out->
			infoLine('Building containers: ');
		
		foreach (MetaClassPull::me()->getList() as $class) {
			foreach ($class->getProperties() as $property) {
				if (
					$property->getRelation()
					&& ($property->getRelationId() != MetaRelation::ONE_TO_ONE)
				) {
					$relation = $class->getName() . ucfirst($property->getName()) . 'DAO';
					$userFile1 = NamespaceUtils::getDAODir($class) . DIRECTORY_SEPARATOR . $relation . EXT_CLASS;
					$userFile =
						ONPHP_META_DAO_DIR
						.$class->getName().ucfirst($property->getName())
						.'DAO'
						.EXT_CLASS;

					if ($force || !file_exists($userFile)) {
						BasePattern::dumpFile(
							$userFile,
							Format::indentize(
								ContainerClassBuilder::buildContainer(
									$class,
									$property
								)
							)
						);
					}
					
					// check for old-style naming
					$oldStlye =
						ONPHP_META_DAO_DIR
						.$class->getName()
						.'To'
						.$property->getType()->getClassName()
						.'DAO'
						.EXT_CLASS;
					
					if (is_readable($oldStlye)) {
						$out->
							newLine()->
							error(
								'remove manually: '.$oldStlye
							);
					}
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * @return MetaConfiguration
	**/
	public function checkIntegrity()
	{
		$out = $this->getOutput()->
			newLine()->
			infoLine('Checking sanity of generated files: ')->
			newLine()->
			info("\t");
		
		$formErrors = array();

		foreach (MetaClassPull::me()->getList() as $name => $class) {
			if (
				!(
					$class->getPattern() instanceof SpookedClassPattern
					|| $class->getPattern() instanceof SpookedEnumerationPattern
					|| $class->getPattern() instanceof SpookedEnumPattern
					|| $class->getPattern() instanceof InternalClassPattern
				) && (
					class_exists(MetaClassNameBuilder::getClassOfMetaClass($class, true))
				)
			) {
				$out->info($name, true);
				
				$className = MetaClassNameBuilder::getClassOfMetaClass($class);
				
				$info = new \ReflectionClass($className);
				
				$this->checkClassSanity($class, $info);
				
				if ($info->implementsInterface(Prototyped::class)) {
					$this->checkClassSanity($class,
						new \ReflectionClass($class->getProtoClass())
					);
				}
				
				if ($info->implementsInterface(DAOConnected::class)) {
					$this->checkClassSanity(
						$class,
						new \ReflectionClass($class->getDaoClass())
					);
				}
				
				foreach ($class->getInterfaces() as $interface)
					Assert::isTrue(
						$info->implementsInterface($interface),
						
						'class '.$class->getName()
						.' expected to implement interface '.$interface
					);
				
				// special handling for Enumeration instances
				if (
					$class->getPattern() instanceof EnumerationClassPattern
					|| $class->getPattern() instanceof EnumClassPattern
				) {
					$object = new $className(call_user_func([$className, 'getAnyId']));
					
					Assert::isTrue(
						unserialize(serialize($object)) == $object
					);
					
					$out->info(', ');
					
					if ($this->checkEnumerationRefIntegrity)
					{
						if(
							$object instanceof Enumeration
							|| $object instanceof Enum
						)
							$this->checkEnumerationReferentialIntegrity(
								$object,
								$class->getTableName()
							);
					}
					
					continue;
				}
				
				if ($class->getPattern() instanceof AbstractClassPattern) {
					$out->info(', ');
					continue;
				}
				
				/** @var Prototyped $object */
				$object = new $className;
				$proto = $object->proto();
				$form = $proto->makeForm();
				
				foreach ($class->getProperties() as $name => $property) {
					Assert::isTrue(
						$property->toLightProperty($class)
							== $proto->getPropertyByName($name),
						
						'defined property does not match autogenerated one - '
						.$class->getName().'::'.$property->getName()
					);
				}
				
				if (!$object instanceof DAOConnected) {
					$out->info(', ');
					continue;
				}
				
				$dao = $object->dao();
				
				Assert::isEqual(
					$dao->getIdName(),
					$class->getIdentifier()->getColumnName(),
					'identifier name mismatch in '.$class->getName().' class'
				);
				
				try {
					DBPool::getByDao($dao);
				} catch (MissingElementException $e) {
					// skipping
					$out->info(', ');
					continue;
				}
				
				$query =
					Criteria::create($dao)->
					setLimit(1)->
					add(Expression::notNull($class->getIdentifier()->getName()))->
					addOrder($class->getIdentifier()->getName())->
					toSelectQuery();
				
				$out->warning(
					' ('
					.$query->getFieldsCount()
					.'/'
					.$query->getTablesCount()
					.'/'
				);
				
				$clone = clone $object;
				
				if (serialize($clone) == serialize($object)) {
					$out->info('C', true);
				} else {
					$out->error('C', true);
				}
				
				$out->warning('/');
				
				try {
					$object = $dao->getByQuery($query);
					$form = $object->proto()->makeForm();
					FormUtils::object2form($object, $form);
					
					if ($errors = $form->getErrors()) {
						$formErrors[$class->getName()] = $errors;
						
						$out->error('F', true);
					} else
						$out->info('F', true);
				} catch (ObjectNotFoundException $e) {
					$out->warning('F');
				}
				
				$out->warning('/');
				
				if (
					Criteria::create($dao)->
						setFetchStrategy(FetchStrategy::cascade())->
						toSelectQuery()
							== $dao->makeSelectHead()
				) {
					$out->info('H', true);
				} else {
					$out->error('H', true);
				}
				
				$out->warning('/');
				
				// cloning once again
				$clone = clone $object;
				
				FormUtils::object2form($object, $form);
				FormUtils::form2object($form, $object);
				
				if ($object != $clone) {
					$out->error('T', true);
				} else {
					$out->info('T', true);
				}
				
				$out->warning(')')->info(', ');
			}
		}
		
		$out->infoLine('done.');
		
		if ($formErrors) {
			$out->newLine()->errorLine('Errors found:')->newLine();
			
			foreach ($formErrors as $class => $errors) {
				$out->errorLine("\t".$class.':', true);
				
				foreach ($errors as $name => $error) {
					$out->errorLine(
						"\t\t".$name.' - '
						.(
							$error == Form::WRONG
								? ' wrong'
								: ' missing'
						)
					);
				}
				
				$out->newLine();
			}
		}
		
		return $this;
	}
	
	/**
	 * @return MetaConfiguration
	**/
	public function checkForStaleFiles($drop = false)
	{
		$this->getOutput()->
			newLine()->
			infoLine('Checking for stale files: ');
		
		foreach( $this->namespaces as $name => $info ) {
			if( $info['build'] == false ) {
				continue;
			}
			$this->checkPathStales($info['classes'], $info['path'], false, $drop);
		}
	}
	
	/**
	 * @throws MissingElementException
	 * @return MetaClass
	**/
	public function getClassByName($name)
	{
		return MetaClassPull::me()->getClass($name);
	}
	
	public function getClassList()
	{
		return MetaClassPull::me()->getList();
	}
	
	public function getNamespaceList() {
		return $this->namespaces;
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
	
	public function makePUML()
	{
		$out = "@startuml\n";
		
		foreach (MetaClassPull::me()->getList() as $metaclass) {
			$out .= MetaClassPUMLGenerator::generate($metaclass);
		}
			
		$out .= MetaClassPUMLGenerator::generateLinks(MetaClassPull::me()->getList());
		
		return $out."@enduml\n";
	}
	
	/**
	 * @return MetaConfiguration
	**/
	private function checkDirectory(
		array $directories, $preStrip, $postStrip, $drop = false
	)
	{
		$out = $this->getOutput();

		foreach($directories as $directory) {
			foreach (
				glob($directory . '*.php', GLOB_NOSORT)
				as $filename
			) {
				$name =
					substr(
						basename($filename, $postStrip . EXT_CLASS),
						strlen($preStrip)
					);

				if (MetaClassPull::me()->hasClass($name)) {
					$out->warning(
						"\t"
						. str_replace(
							getcwd() . DIRECTORY_SEPARATOR,
							null,
							$filename
						)
					);

					if ($drop) {
						try {
							unlink($filename);
							$out->infoLine(' removed.');
						} catch (BaseException $e) {
							$out->errorLine(' failed to remove.');
						}
					} else {
						$out->newLine();
					}
				}
			}
		}
		
		return $this;
	}
	
	private function checkPathStales(array $classes ,$path, $auto = false, $drop = false) {
		if( !is_dir($path) ) {
			return;
		}
		$path .= DIRECTORY_SEPARATOR;

		$list = scandir($path);
		if (is_array($list)) {
			foreach ($list as $item) {
				if ( $item[0] == '.' ) {
					continue;
				}

				if ( !is_dir($path.$item) ) {
					continue;
				}

				switch($item) {
					case 'Auto': {
						$this->checkPathStales($classes, $path.$item, true, $drop);
					} break;
					case 'Business': {
						$this->checkDirectory($classes, $path.$item, ($auto?'Auto':''), null, $drop, $auto);
					} break;
					case 'DAO': {
						$this->checkDirectory($classes, $path.$item, ($auto?'Auto':''), 'DAO', $drop, $auto);
					} break;
					case 'Proto': {
						$this->checkDirectory($classes, $path.$item, ($auto?'AutoProto':'Proto'), null, $drop, $auto);
					} break;
					default: {
						if( $auto ) {
							$this->getOutput()->warning("\t" . $path.$item);
						}
					}
				}
			}
		}
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
	* @return MetaConfiguration
	**/
	private function addNamespace(\SimpleXMLElement $source) {
		$name = (string)$source['name'];
		$path = (string)$source['path'];

		$this->putNamesapce($name, BASE_PATH.$path);

		return $this;
	}

	/**
	 * @return MetaConfiguration
	 **/
	private function addUseItem(\SimpleXMLElement $source) {
		$name = (string)$source['name'];
		$import = (string)$source['import'];
		
		UsesPull::me()->addClass($name, $import);
		
		return $this;
	}
	
	/**
	 * @param string $name
	 *
	 * @return MetaConfiguration
	 * @throws WrongStateException
	 */
	private function guessNamespace($name) {
		$nsparts = explode('\\', $name);

		$dirparts = [];
		$path = null;
		while (count($nsparts) > 0) {
			$check = implode('\\', $nsparts);
			if (isset($this->namespaces[$check])) {
				$path = $this->namespaces[$check]['path'];
				break;
			} else {
				$dirparts[] = array_pop($nsparts);
			}
		}

		if (is_null($path)) {
			throw new WrongStateException(
				"Unknown namespace `{$name}`, can not guess path"
			);
		}

		array_unshift($dirparts, $path);

		$this->putNamesapce($name, implode(DIRECTORY_SEPARATOR, $dirparts));

		return $this;
	}

	/**
	 * @return MetaConfiguration
	 */
	private function putNamesapce($name, $path) {
		Assert::isFalse(isset($this->namespaces[$name]), "duplicate namespace - '{$name}'");

		$this->namespaces[$name] = [
			'path' => str_replace('/./', '/', $path),
			'build' => true,
			'classes' => [],
		];

		return $this;
	}

	/**
	 * @return MetaClassProperty
	**/
	private function makeProperty($name, $type, MetaClass $class, $size)
	{
		Assert::isFalse(
			strpos($name, '_'),
			'naming convention violation spotted'
		);
		
		if (!$name || !$type)
			throw new WrongArgumentException(
				'strange name or type given: "'.$name.'" - "'.$type.'"'
			);
		
		$typeClass = '\OnPHP\Meta\Type\\' . $type . 'Type';
		
		if (!class_exists($typeClass)) {
			$typeClass = '\OnPHP\Meta\Type\ObjectType';
		}
		
		$property = new MetaClassProperty($name, new $typeClass($type), $class);
		
		if ($size) {
			$property->setSize($size);
		} else {
			Assert::isTrue(
				(!$property->getType() instanceof FixedLengthStringType) 
				&& (!$property->getType() instanceof NumericType) 
				&& (!$property->getType() instanceof HttpUrlType),
				'size is required for "'.$property->getName().'"'
			);
		}
		
		return $property;
	}
	
	/**
	 * @throws MissingElementException
	 * @return GenerationPattern
	**/
	private function guessPattern($name)
	{
		$class = '\OnPHP\Meta\Pattern\\'.$name.'Pattern';
		
		if (!class_exists($class)) {
			throw new MissingElementException("unknown pattern '{$name}'");
		}

		return Singleton::getInstance($class);
	}
	
	/**
	 * @return MetaConfiguration
	**/
	private function checkSanity(MetaClass $class)
	{
		if (
			(
				!$class->getParent()
				|| $class->getFinalParent()->isInternal()
			)
			&& (!$class->getPattern() instanceof ValueObjectPattern)
			&& (!$class->getPattern() instanceof InternalClassPattern)
		) {
			Assert::isTrue(
				$class->getIdentifier() !== null,
				'only value objects can live without identifiers. '
				.'do not use them anyway ('
				.$class->getName().')'
			);
		}
		
		if (
			$class->getType()
			&& $class->getTypeId()
				== MetaClassType::CLASS_SPOOKED
		) {
			Assert::isFalse(
				count($class->getProperties()) > 1,
				'spooked classes must have only identifier: '
				.$class->getName()
			);
			
			Assert::isTrue(
				($class->getPattern() instanceof SpookedClassPattern
				|| $class->getPattern() instanceof SpookedEnumerationPattern
				|| $class->getPattern() instanceof SpookedEnumPattern),
				'spooked classes must use spooked patterns only: '
					.$class->getName()
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
					'optional value object is not supported:'
					.$property->getName().' @ '.$class->getName()
				);
				
				Assert::isTrue(
					$property->getRelationId() == MetaRelation::ONE_TO_ONE,
					'value objects must have OneToOne relation: '
					.$property->getName().' @ '.$class->getName()

				);
			} elseif (
				($property->getFetchStrategyId() == FetchStrategy::LAZY)
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
		
		if (
			$property->getFetchStrategy()
			&& $property->getFetchStrategy()->getId() != FetchStrategy::JOIN
		) {
			return false;
		}

		$remote = $property->getType()->getClass();
		
		if (isset($paths[$holder->getName()][$remote->getName()])) {
			return true;
		} else {
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
	
	/**
	 * @return MetaConfiguration
	**/
	private function processIncludes(\SimpleXMLElement $xml, $metafile)
	{
		foreach ($xml->include as $include) {
			$file = (string) $include['file'];
			$path = dirname($metafile).'/'.$file;
			
			Assert::isTrue(
				is_readable($path),
				'can not include '.$file
			);
			
			$this->getOutput()->
				infoLine('Including "'.$path.'".')->
				newLine();
			
			$this->loadXml($path, !((string) $include['generate'] == 'false'));
		}
		
		return $this;
	}
	
	/**
	 * @return MetaConfiguration
	**/
	private function processClasses(\SimpleXMLElement $xml, $metafile, $generate)
	{
		$attrs = $xml->attributes();
		
		if (!isset($attrs['namespace'])) {
			throw new WrongStateException('Element classes should contain a `namespace` attribute');
		}
		
		$namespace = strval($attrs['namespace']);
		
		NamespaceUtils::checkNS($namespace);
		
		if(!isset($this->namespaces[$namespace]) ) {
			$this->guessNamespace($namespace);
		}
		
		foreach ($xml as $xmlClass) {
			$name = (string) $xmlClass['name'];
			$this->namespaces[$namespace]['classes'][] = $name;
			
			Assert::isFalse(
				MetaClassPull::me()->hasClass($name),
				'class name collision found for '.$name
			);
			
			$class = new MetaClass($name, $namespace);
			
			if (isset($xmlClass['source'])) {
				$class->setSourceLink((string) $xmlClass['source']);
			}
			
			if (isset($xmlClass['table'])) {
				$class->setTableName((string) $xmlClass['table']);
			}
			
			if (isset($xmlClass['type'])) {
				$type = (string) $xmlClass['type'];
				
				if ($type == 'spooked') {
					$this->getOutput()->
						warning($class->getName(), true)->
						warningLine(': uses obsoleted "spooked" type.')->
						newLine();
				}
				
				$class->setType(
					new MetaClassType(
						(string) $xmlClass['type']
					)
				);
			}

			// lazy existence checking
			if (isset($xmlClass['extends'])) {
				$this->liaisons[$class->getName()] = (string) $xmlClass['extends'];
			}
			
			// populate implemented interfaces
			foreach ($xmlClass->implement as $xmlImplement) {
				$class->addInterface((string) $xmlImplement['interface']);
			}
			
			if (isset($xmlClass->properties[0]->identifier)) {
				
				$id = $xmlClass->properties[0]->identifier;
				
				if (!isset($id['name'])) {
					$name = 'id';
				} else {
					$name = (string) $id['name'];
				}
				
				if (!isset($id['type'])) {
					$type = 'BigInteger';
				} else {
					$type = (string) $id['type'];
				}
				
				$property = $this->makeProperty(
					$name,
					$type,
					$class,
					// not casting to int because of Numeric possible size
					(string) $id['size']
				);
				
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
			
			if ((string) $xmlClass->pattern['fetch'] == 'cascade') {
				$class->setFetchStrategy(FetchStrategy::cascade());
			}
			
			if ($class->isInternal()) {
				Assert::isTrue(
					$metafile === ONPHP_META_PATH.'internal.xml',
					'internal classes can be defined only in OnPHP, sorry'
				);
				
				UsesPull::me()->addClass($class->getName(), $class->getNameWithNS(false));
			} elseif (
				$class->getPattern() instanceof SpookedClassPattern
				|| $class->getPattern() instanceof SpookedEnumerationPattern
				|| $class->getPattern() instanceof SpookedEnumPattern
			) {
				$class->setType(
					new MetaClassType(
						MetaClassType::CLASS_SPOOKED
					)
				);
			}
			
			// populate properties
			foreach ($xmlClass->properties[0] as $xmlProperty) {
				
				$property = $this->makeProperty(
					(string) $xmlProperty['name'],
					(string) $xmlProperty['type'],
					$class,
					(string) $xmlProperty['size']
				);
				
				if (isset($xmlProperty['column'])) {
					$property->setColumnName(
						(string) $xmlProperty['column']
					);
				} elseif (
					($property->getType() instanceof ObjectType)
					&& (!$property->getType()->isGeneric())
				) {
					if (MetaClassPull::me()->
							hasClass(
								$property->getType()->getClassName()
							)
						&& (
							$property->getType()->getClass()->isInternal()
						)
					) {
						throw new UnimplementedFeatureException(
							'you can not use internal classes directly atm'
						);
					}
					
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
				
				if (!$property->getType()->isGeneric()) {
					
					if (!isset($xmlProperty['relation'])) {
						throw new MissingElementException(
							'relation should be set for non-generic '
							."property '{$property->getName()}' type '"
							.get_class($property->getType())."'"
							." of '{$class->getName()}' class"
						);
					} else {
						$property->setRelation(
							MetaRelation::makeFromName(
								(string) $xmlProperty['relation']
							)
						);
						
						if ($fetch = (string) $xmlProperty['fetch']) {
							Assert::isTrue(
								$property->getRelationId()
								== MetaRelation::ONE_TO_ONE,
								
								'fetch mode can be specified '
								.'only for OneToOne relations'
							);
							
							if ($fetch == 'lazy') {
								$property->setFetchStrategy(
									FetchStrategy::lazy()
								);
							} elseif ($fetch == 'cascade') {
								$property->setFetchStrategy(
									FetchStrategy::cascade()
								);
							} else {
								throw new WrongArgumentException(
									'strange fetch mode found - '.$fetch
								);
							}
						}
						
						if (
							(
								$property->getRelationId() == MetaRelation::ONE_TO_ONE
								 && $property->getFetchStrategyId() != FetchStrategy::LAZY
							) && (
								$property->getType()->getClassName()
								<> $class->getName()
							)
						) {
							$this->references[$property->getType()->getClassName()][]
									= $class->getBusinessClass();
							
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
			
			$class->setBuild($generate);
			
			MetaClassPull::me()->addClass($class);
		}
		
		return $this;
	}
	
	private function loadXml($metafile, $generate)
	{
		$contents = file_get_contents($metafile);
		
		$contents = str_replace(
			'"meta.dtd"',
			'"'.ONPHP_META_PATH.'meta.dtd"',
			$contents
		);
		
		$doc = new \DOMDocument('1.0');
		$doc->loadXML($contents);
		
		try {
			$doc->validate();
		} catch (BaseException $e) {
			$error = libxml_get_last_error();
			throw new WrongArgumentException(
				$error->message.' in node placed on line '
				.$error->line.' in file '.$metafile
			);
		}
		
		$xml = simplexml_import_dom($doc);
		
		// populate sources (if any)
		if (isset($xml->sources[0])) {
			foreach ($xml->sources[0] as $source) {
				$this->addSource($source);
			}
		}
		
		// populate namespaces (if any)
		if (isset($xml->namespaces[0])) {
			foreach ($xml->namespaces[0] as $namespace) {
				$this->addNamespace($namespace);
			}
		}
		
		// populate uses (if any)
		if (isset($xml->uses[0])) {
			foreach ($xml->uses[0] as $use) {
				$this->addUseItem($use);
			}
		}
		
		if (isset($xml->include['file'])) {
			$this->processIncludes($xml, $metafile);
		}
		
		// otherwise it's an includes-only config
		foreach ($xml->classes as $classesNode) {
			$this->processClasses($classesNode, $metafile, $generate);
		}
		
		return $this;
	}
	
	/**
	 * @return MetaConfiguration
	**/
	private function checkClassSanity(
		MetaClass $class,
		\ReflectionClass $info
	)
	{
		switch ($class->getTypeId()) {
			case null:
				break;
			
			case MetaClassType::CLASS_ABSTRACT:
				Assert::isTrue(
					$info->isAbstract(),
					'class '.$info->getName().' expected to be abstract'
				);
				Assert::isTrue(
					$class->getPattern() instanceof AbstractClassPattern,
					'class '.$info->getName().' must use AbstractClassPattern'
				);
				break;
			
			case MetaClassType::CLASS_FINAL:
				Assert::isTrue(
					$info->isFinal(),
					'class '.$info->getName().' expected to be final'
				);
				break;
			
			case MetaClassType::CLASS_SPOOKED:
			default:
				Assert::isUnreachable();
				break;
		}
		
		if ($public = $info->getProperties(ReflectionProperty::IS_PUBLIC)) {
			Assert::isUnreachable(
				$class->getName()
				.' contains properties with evil visibility:'
				."\n"
				.print_r($public, true)
			);
		}
		
		return $this;
	}
	
	private function checkEnumerationReferentialIntegrity(
		$enumeration, $tableName
	)
	{
		Assert::isTrue(
			(
				$enumeration instanceof Enumeration
				|| $enumeration instanceof Enum
			),
			'argument enumeation must be instacne of Enumeration or Enum! gived, "'.gettype($enumeration).'"'
		);

		$updateQueries = null;
		
		$db = DBPool::me()->getLink();
		
		$class = get_class($enumeration);
		
		$ids = array();

		if ($enumeration instanceof Enumeration) {
			$list = $enumeration->getList();
		} elseif ($enumeration instanceof Enum) {
			$list = ClassUtils::callStaticMethod($class.'::getList');
		}
		
		foreach ($list as $enumerationObject) {
			$ids[$enumerationObject->getId()] = $enumerationObject->getName();
		}
		
		$rows =
			$db->querySet(
				OSQL::select()->from($tableName)->
					multiGet('id', 'name')
			);
		
		echo "\n";
		
		foreach ($rows as $row) {
			if (!isset($ids[$row['id']])) {
				echo "Class '{$class}', strange id: {$row['id']} found. \n";
			} else {
				if ($ids[$row['id']] != $row['name']) {
					echo "Class '{$class}',id: {$row['id']} sync names. \n";
					
					$updateQueries .=
						OSQL::update($tableName)->
							set('name', $ids[$row['id']])->
							where(Expression::eq('id', $row['id']))->
							toDialectString($db->getDialect()) . ";\n";
				}
				
				unset($ids[$row['id']]);
			}
		}
		
		foreach ($ids as $id => $name) {
			echo "Class '{$class}', id: {$id} not present in database. \n";
		}
		
		echo $updateQueries;
		
		return $this;
	}
}
?>