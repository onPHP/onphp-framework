<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
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
		public static function build(MetaClass $class)
		{
			$out = <<<EOT
\$schema->
	addTable(
		DBTable::create('{$class->getDumbName()}')->

EOT;

			$columns = array();
			
			foreach ($class->getProperties() as $name => $property) {
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
					
					$foreignClass =
						MetaConfiguration::me()->getClassByName(
							$property->getType()->getClass()
						);
					
					$sourceTable = $class->getDumbName();
					$sourceColumn = $name.'_id';
					
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