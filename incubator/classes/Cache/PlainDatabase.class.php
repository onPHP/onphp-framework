<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@shadanakar.org                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class PlainDatabase extends CachePeer
	{
		private $db		= null;
		private $table	= null;
		
		public function __construct(DB $db, $tableName)
		{
			if (!defined('__I_HATE_MY_KARMA__'))
				throw new UnsupportedMethodException(
					'this is only example. do not ever use it.'
				);
			
			$this->db		= $db;
			$this->table	= $tableName;
		}
		
		public function isAlive()
		{
			return $this->db->isConnected();
		}
		
		public function get($key)
		{
			try {
				$this->cleanUp();

				$data =
					current( 
						$this->db->queryRow(
							OSQL::select()->from($this->table)->
							get('data')->
							where(
								Expression::eq(
									'id', $this->toInteger($key)
								)
							)
						)
					);

				return $this->restoreData($data);				
					
			} catch (DatabaseException $e) {
				return null;
			}
		}
		
		public function delete($key)
		{
			try {
				$this->db->queryNull(
					OSQL::delete()->from($this->table)->
					where(
						Expression::eq(
							'id', $this->toInteger($key)
						)
					)
				);
				
				return true;
			} catch (DatabaseException $e) {
				return false;
			}
		}

		protected function store($action, $key, &$value, $expires = 0)
		{
			$id = $this->toInteger($key);
			
			$this->cleanUp();
			
			if (!$expires)
				$expires = null;
			
			try {
				$this->db->queryRow(
					OSQL::select()->from($this->table)->
					get('id')->
					where(
						Expression::eq('id', $id)
					)
				);
				
				if ($action == 'add')
					return true;
					
				$query = OSQL::update();
				
			} catch (ObjectNotFoundException $e) {
				if ($action == 'replace')
					return false;
				
				$query = OSQL::insert();
			}
			
			$query->
				setTable($this->table)->
				set('id', $id)->
				set('data', $this->prepareData($value))->
				set('expires', $expires);
			
			try {
				$this->queryNull(
					$query
				);
			} catch (DatabaseException $e) {
				return false;
			}
			
			return true;
		}

		private function toInteger($key)
		{
			// 2147483647
			return printf('%u', substr(sha1($key, 0, 10)));
		}
		
		private function cleanUp()
		{
			$this->db->queryNull(
				OSQL::delete()->from($this->table)->
				where(
					Expression::expAnd(
						Expression::ltEq('expires', time()),
						Expression::notNull('expire')
					)
				)
			);
		}
	}
?>