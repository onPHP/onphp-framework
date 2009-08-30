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
	final class SchemaBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			if (!count($class->getProperties()))
				return null;
			
			$out = <<<EOT
\$schema->
	addTable(
		DBTable::create('{$class->getDumbName()}')->

EOT;

			$columns = array();
			
			foreach ($class->getProperties() as $name => $property) {
				if (
					($relation = $property->getRelation())
					&& $relation->getId() <> MetaRelation::ONE_TO_ONE
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
		
		// TODO: implement ManyTo{One,Many} relations
		public static function buildRelations(MetaClass $class)
		{
			$out = null;
			
			foreach ($class->getProperties() as $name => $property) {
				if ($relation = $property->getRelation()) {
					
					if (
						$relation->getId() <> MetaRelation::ONE_TO_ONE
					) {
						continue;
					}
					
					$foreignClass =
						MetaConfiguration::me()->getClassByName(
							$property->getType()->getClass()
						);
					
					$sourceTable = $class->getDumbName();
					$sourceColumn = $property->getDumbName().'_id';
					
					$targetTable = $foreignClass->getDumbName();
					$targetColumn = $foreignClass->getIdentifier()->getDumbName();
					
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