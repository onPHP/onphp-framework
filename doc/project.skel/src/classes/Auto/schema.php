<?php
/*****************************************************************************
 *   Copyright (C) 2006-2007, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-0.9.128 at 2007-03-31 16:25:48                       *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	$schema = new DBSchema();
	
	$schema->
		addTable(
			DBTable::create('administrator')->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::BIGINT)->
					setNull(false),
					'id'
				)->
				setPrimaryKey(true)->
				setAutoincrement(true)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setNull(false)->
					setSize(64),
					'username'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setNull(false)->
					setSize(40),
					'password'
				)
			)
		);
	
?>