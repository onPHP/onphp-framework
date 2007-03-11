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
	final class SchemaBuilder extends BaseBuilder
	{
		public static function buildTable($tableName, /* array */ $propertyList)
		{
			$out = <<<EOT
\$schema->
	addTable(
		DBTable::create('{$tableName}')->

EOT;

			$columns = array();
			
			foreach ($propertyList as $property) {
				if (
					($relation = $property->getRelation())
					&& (
						$relation->getId() != MetaRelation::ONE_TO_ONE
						&& $relation->getId() != MetaRelation::LAZY_ONE_TO_ONE
					)
				) {
					continue;
				}
				
				$column = $property->toColumn();
				
				if (is_array($column))
					$columns = array_merge($columns, $column);
				else
					$columns[] = $property->toColumn();
			}
			
			$out .= implode("->\n", $columns);
			
			return $out."\n);\n\n";
		}
		
		public static function buildRelations(MetaClass $class)
		{
			$out = null;
			
			foreach ($class->getAllProperties() as $property) {
				if ($relation = $property->getRelation()) {
					
					$foreignClass = $property->getType()->getClass();
					
					if (
						$relation->getId() == MetaRelation::ONE_TO_MANY
						// nothing to build, it's in the same table
						|| (
							$foreignClass->getPattern() instanceof ValueObjectPattern
						)
					) {
						continue;
					} elseif ($relation->getId() == MetaRelation::MANY_TO_MANY) {
						$tableName =
							$class->getTableName()
							.'_'
							.$foreignClass->getTableName();
						
						$foreignPropery = clone $foreignClass->getIdentifier();
						
						$name = $class->getName();
						$name = strtolower($name[0]).substr($name, 1);
						$name .= 'Id';
						
						$foreignPropery->
							setName($name)->
							setColumnName($foreignPropery->getConvertedName())->
							// we don't need primary key here
							setIdentifier(false);
						
						$out .= <<<EOT
\$schema->
	addTable(
		DBTable::create('{$tableName}')->
		{$property->toColumn()}->
		{$foreignPropery->toColumn()}->
		addUniques('{$class->getTableName()}_id', '{$foreignClass->getTableName()}_id')
	);


EOT;
					} else {
						$sourceTable = $class->getTableName();
						$sourceColumn = $property->getColumnName();
						
						$targetTable = $foreignClass->getTableName();
						$targetColumn = $foreignClass->getIdentifier()->getColumnName();
						
						$out .= <<<EOT
// {$sourceTable}.{$sourceColumn} -> {$targetTable}.{$targetColumn}
\$schema->
	getTableByName('{$sourceTable}')->
		getColumnByName('{$sourceColumn}')->
			setReference(
				\$schema->
					getTableByName('{$targetTable}')->
					getColumnByName('{$targetColumn}'),
				ForeignChangeAction::restrict(),
				ForeignChangeAction::cascade()
			);


EOT;
					
					}
				}
			}
			
			return $out;
		}
		
		public static function getHead()
		{
			$out = parent::getHead();
			
			$out .= "\$schema = new DBSchema();\n\n";
			
			return $out;
		}
	}
?>