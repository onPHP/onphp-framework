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
	 * @ingroup Builders
	**/
	abstract class BaseBuilder extends StaticFactory
	{
		public static function build(MetaClass $class)
		{
			throw new UnimplementedFeatureException('i am forgotten method');
		}
		
		protected static function buildFillers(MetaClass $class)
		{
			$out = null;
			
			$className = $class->getName();
			$varName = strtolower($className[0]).substr($className, 1);

			$setters = array();
			$valueObjects = array();
			
			$standaloneFillers = array();
			$chainFillers = array();
			
			$joinedStandaloneFillers = array();
			$joinedChainFillers = array();
			
			$cascadeStandaloneFillers = array();
			$cascadeChainFillers = array();
			
			foreach ($class->getProperties() as $property) {
				
				if (
					$property->getRelationId() == MetaRelation::ONE_TO_ONE
					&& (
						!$property->getType()->isGeneric()
						&& $property->getType() instanceof ObjectType
						&& (
							$property->getType()->getClass()->getPattern()
								instanceof ValueObjectPattern
						)
					)
				) {
					$cascadeChainFillers[] = $property->toDaoSetter($className, true);
					$joinedChainFillers[] = $property->toDaoSetter($className, false);
					
					$valueObjects[ucfirst($property->getName())] =
						$property->getType()->getClassName();
				} elseif (
					$property->getRelationId() == MetaRelation::ONE_TO_ONE
				) {
					$buildSetter = false;
					
					if ($filler = $property->toDaoSetter($className, true)) {
						self::processFiller(
							$property,
							$cascadeStandaloneFillers,
							$cascadeChainFillers,
							$filler
						);
						
						$buildSetter = true;
					}
					
					if ($property->getFetchStrategyId() == FetchStrategy::CASCADE) {
						$nonJoin = true;
					} else {
						$nonJoin = false;
					}
					
					if ($filler = $property->toDaoSetter($className, $nonJoin)) {
						if ($nonJoin) {
							if (
								(
									$property->getType()->getClass()->getPattern()
										instanceof SpookedClassPattern
								) || (
									$property->getType()->getClass()->getPattern()
										instanceof SpookedEnumerationPattern
								)
							) {
								$prefix =
									'// forcing cascade strategy '
									.'due to spooked property'."\n";
							} else {
								$prefix =
									'// forcing cascade strategy due to recursion'
									."\n";
							}
						} else
							$prefix = null;
						
						// TODO: make it sane
						if (substr($filler, 0, 2) == 'if') {
							$joinedStandaloneFillers[] = $prefix.$filler;
						} else {
							$joinedChainFillers[] = $prefix.$filler;
						}
						
						$buildSetter = true;
					}
					
					if ($buildSetter)
						$setters[] = $property->toDaoField($className);
				} else {
					$filler = $property->toDaoSetter($className);
					
					if ($filler !== null) {
						
						$setters[] = $property->toDaoField($className);
						
						self::processFiller(
							$property,
							$standaloneFillers,
							$chainFillers,
							$filler
						);
					}
				}
			}
			
			if ($valueObjects) {
				foreach ($valueObjects as $valueName => $valueClass) {
					$out .=
						"Singleton::getInstance('{$valueClass}DAO')->"
						."setQueryFields(\$query, \${$varName}->get{$valueName}());\n";
				}
				
				$out .= "\n";
			}
			
			if ($class->getPattern() instanceof ValueObjectPattern)
				$visibility = 'public';
			else
				$visibility = 'protected';
			
			$out .= <<<EOT
		return
			\$query->

EOT;
			$out .= implode("->\n", $setters).";\n";

			$out .= <<<EOT
		}

EOT;
			if ($class->getTypeId() == MetaClassType::CLASS_ABSTRACT) {
				$out .= <<<EOT

protected function fillSelf({$className} \${$varName}, &\$array, \$prefix = null)
{

EOT;
			} else {
				$out .= <<<EOT

/**
 * @return {$className}
**/
{$visibility} function makeSelf(&\$array, \$prefix = null)
{

EOT;
				if ($class->getParent()) {
					if (
						$class->getParent()->getTypeId()
						== MetaClassType::CLASS_ABSTRACT
					) {
						$out .= <<<EOT
\${$varName} = parent::fillSelf(new {$className}(), \$array, \$prefix);


EOT;
					} else {
						$out .= <<<EOT
\${$varName} = parent::makeSelf(\$array, \$prefix);


EOT;
					}
				} else {
					if ($class->hasChilds()) {
						$out .= <<<EOT
\$className = \$this->getObjectName();
\${$varName} = new \$className;


EOT;
					} else {
						$out .= <<<EOT
\${$varName} = new {$className}();


EOT;
					}
				}
			}
			
			if ($chainFillers) {
				
				$out .= "\${$varName}->\n";
				
				$out .= implode("->\n", $chainFillers).";\n\n";
			}
			
			if ($standaloneFillers) {
				$out .= implode("\n", $standaloneFillers)."\n";
			}

			$out .= <<<EOT
			return \${$varName};
		}

EOT;
			if ($cascadeChainFillers || $cascadeStandaloneFillers) {
				$out .= <<<EOT

/**
 * @return {$className}
**/
{$visibility} function makeCascade(/* {$className} */ \${$varName}, &\$array, \$prefix = null)
{

EOT;
				if ($class->getParent()) {
					$out .= <<<EOT
\${$varName} = parent::makeCascade(\${$varName}, \$array, \$prefix);


EOT;
				}
				
				if ($cascadeChainFillers) {
					$out .= "\${$varName}->\n";
					
					$out .= implode("->\n", $cascadeChainFillers).";\n\n";
				}
				
				if ($cascadeStandaloneFillers) {
					$out .= implode("\n", $cascadeStandaloneFillers)."\n";
				}
				
				$out .= <<<EOT
return \${$varName};
}

EOT;
			}
			
			if ($joinedChainFillers || $joinedStandaloneFillers) {
				if ($joinedChainFillers)
					$chain = implode("->\n", $joinedChainFillers).';';
				else
					$chain = null;
				
				if ($joinedStandaloneFillers)
					$alone = implode("\n", $joinedStandaloneFillers);
				else
					$alone = null;
				
				$out .= <<<EOT

/**
 * @return {$className}
**/
{$visibility} function makeJoiners(/* {$className} */ \${$varName}, &\$array, \$prefix = null)
{

EOT;
				if ($class->getParent()) {
					$out .= <<<EOT
\${$varName} = parent::makeJoiners(\${$varName}, \$array, \$prefix);


EOT;
				}
				
				if ($chain) {
					$out .= <<<EOT
\${$varName}->
	{$chain}


EOT;
				}
				
				if ($alone)
					$out .= $alone."\n";
				
				$out .= <<<EOT
return \${$varName};
}

EOT;
			}
			
			$out .= <<<EOT
}

EOT;
			
			return $out;
		}
		
		protected static function buildPointers(MetaClass $class)
		{
			$out = null;
			
			if (!$class->getPattern() instanceof AbstractClassPattern) {
				if (
					$class->getIdentifier()->getName() !== 'id'
				) {
					$out .= <<<EOT
public function getIdName()
{
	return '{$class->getIdentifier()->getName()}';
}

EOT;
				}
				
				$out .= <<<EOT
public function getTable()
{
	return '{$class->getTableName()}';
}

public function getObjectName()
{
	return '{$class->getName()}';
}

public function getSequence()
{
	return '{$class->getTableName()}_id';
}
EOT;
			} else {
				$out .= <<<EOT
// no get{Table,ObjectName,Sequence} for abstract class
EOT;
			}
			
			if ($liaisons = $class->getReferencingClasses()) {
				$uncachers = array();
				foreach ($liaisons as $className) {
					$uncachers[] = $className.'::dao()->uncacheLists();';
				}
				
				$uncachers = implode("\n", $uncachers);
				
				$out .= <<<EOT


public function uncacheLists()
{
{$uncachers}

return parent::uncacheLists();
}
EOT;
			}
			
			if ($source = $class->getSourceLink()) {
				$out .= <<<EOT


public function getLinkName()
{
	return '{$source}';
}

EOT;
			}
			
			return $out;
		}
		
		protected static function buildMapping(MetaClass $class)
		{
			$mapping = array();
			
			foreach ($class->getProperties() as $property) {
				
				$row = null;
				
				if ($property->getType()->isGeneric()) {
					
					$name = $property->getName();
					$columnName = $property->getColumnName();
					
					if ($property->getType() instanceof RangeType) {
						
						$row =
							array(
								"'{$name}Min' => '{$columnName}_min'",
								"'{$name}Max' => '{$columnName}_max'"
							);
						
					} else {
						if ($name == $columnName)
							$map = 'null';
						else
							$map = "'{$columnName}'";
						
						$row .= "'{$name}' => {$map}";
					}
				} else {
					
					$relation = $property->getRelation();
					
					if (
						$relation->getId() == MetaRelation::ONE_TO_ONE
						|| $relation->getId() == MetaRelation::LAZY_ONE_TO_ONE
					) {
						$remoteClass = $property->getType()->getClass();
						
						if ($remoteClass->getPattern() instanceof ValueObjectPattern) {
							$row = self::buildMapping($remoteClass);
						} else {
							$row .=
								"'{$property->getName()}"
								."' => '{$property->getColumnName()}'";
						}
					} else
						$row = null;
				}
				
				if ($row) {
					if (is_array($row))
						$mapping = array_merge($mapping, $row);
					else // string
						$mapping[] = $row;
				}
			}
			
			return $mapping;
		}
		
		protected static function getHead()
		{
			$head = self::startCap();
			
			$head .=
				' *   This file is autogenerated - do not edit.'
				.'                               *';

			return $head."\n".self::endCap();
		}
		
		protected static function startCap()
		{
			$version = ONPHP_VERSION;
			$date = date('Y-m-d H:i:s');
			
			$info = " *   Generated by onPHP-{$version} at {$date}";
			$info = str_pad($info, 77, ' ', STR_PAD_RIGHT).'*';
			
			$cap = <<<EOT
<?php
/*****************************************************************************
 *   Copyright (C) 2006-2007, onPHP's MetaConfiguration Builder.             *
{$info}

EOT;

			return $cap;
		}
		
		protected static function endCap()
		{
			$cap = <<<EOT
 *****************************************************************************/
/* \$Id\$ */


EOT;
			return $cap;
		}
		
		protected static function getHeel()
		{
			return '?>';
		}
		
		/* void */ private static function processFiller(
			MetaClassProperty $property,
			/* array */ &$standaloneFillers,
			/* array */ &$chainFillers,
			$filler
		)
		{
			if (
				(
					!$property->getType()->isGeneric()
					|| $property->getType() instanceof ObjectType
				)
				&& !$property->isRequired()
				&& !$property->getType() instanceof RangeType
				&& !(
					$property->getType() instanceof ObjectType
					&& !$property->getType()->isGeneric()
					&& (
						$property->getType()->getClass()->getPattern()
							instanceof ValueObjectPattern
					)
				)
			)
				$standaloneFillers[] =
					implode(
						"\n",
						explode("\n", $filler)
					);
			else
				$chainFillers[] =
					implode(
						"\n",
						explode("\n", $filler)
					);
		}
	}
?>