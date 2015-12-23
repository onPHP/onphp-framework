<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Workaround for sequenceless DB's.
	 * 
	 * You should follow two conventions, when stornig objects thru this one:
	 * 
	 * 1) objects should be childs of IdentifiableObject;
	 * 2) sequence name should equal table name + '_id'.
	 * 
	 * @see IdentifiableOjbect
	 * 
	 * @see MySQL
	 * @see SQLite
	 * 
	 * @ingroup DB
	**/
	abstract class Sequenceless extends DB
	{
		protected $sequencePool = array();
		
		abstract protected function getInsertId();

		/**
		 * @return Identifier
		**/
		final public function obtainSequence($sequence)
		{
			$id = new Identifier;
			
			$this->sequencePool[$sequence][] = $id;
			
			return $id;
		}
		
		final public function query(Query $query)
		{
			$result = $this->queryRaw(
				$query->toDialectString($this->getDialect())
			);
			
			if (
				($query instanceof InsertQuery)
				&& !empty($this->sequencePool[$name = $query->getTable().'_id'])
			) {
				$id = current($this->sequencePool[$name]);
				
				Assert::isTrue(
					$id instanceof Identifier,
					'identifier was lost in the way'
				);
				
				$id->setId($this->getInsertId())->finalize();
				
				unset(
					$this->sequencePool[
						$name
					][
						key($this->sequencePool[$name])
					]
				);
			}
			
			return $result;
		}
	}
?>