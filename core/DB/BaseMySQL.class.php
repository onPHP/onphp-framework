<?php
/***************************************************************************
 *   Copyright (C) 2013 by Vasily M. Stashko                               *
 *   vstashko@mail.ru                                                      *
 ***************************************************************************/

	/**
	 * MySQL DB connector.
	 * 
	 * @see http://www.mysql.com/
	 * @see http://www.php.net/mysql
	 * @see http://www.php.net/mysqli
	 * 
	 * @ingroup DB
	**/
	abstract class BaseMySQL extends Sequenceless
	{
		/**
		 * @return BaseMySQL
		**/
		public function disconnect()
		{
			if ($this->isConnected())
				self::$aliases['close']($this->link);

			return $this;
		}
		
		public function isConnected()
		{
			return parent::isConnected() && self::$aliases['ping']($this->link);
		}
		
		/**
		 * Same as query, but returns number of
		 * affected rows in insert/update queries
		**/
		public function queryCount(Query $query)
		{
			$this->queryNull($query);
			
			return self::$aliases['affected_rows']($this->link);
		}
		
		public function queryRow(Query $query)
		{
			$res = $this->query($query);
			
			if ($this->checkSingle($res))
				return self::$aliases['fetch_assoc']($res);
			else
				return null;
		}
		
		public function queryColumn(Query $query)
		{
			$res = $this->query($query);
			
			if ($res) {
				$array = array();

				while ($row = self::$aliases['fetch_row']($res))
					$array[] = $row[0];

				return $array;
			} else
				return null;
		}
		
		public function querySet(Query $query)
		{
			$res = $this->query($query);
			
			if ($res) {
				$array = array();

				while ($row = self::$aliases['fetch_assoc']($res))
					$array[] = $row;

				return $array;
			} else
				return null;
		}
		
		public function queryRaw($queryString)
		{
			if (!$result = self::$aliases['query']($this->link, $queryString)) {
				
				$code = self::$aliases['errno']($this->link);
				
				if ($code == 1062)
					$e = 'DuplicateObjectException';
				else
					$e = 'DatabaseException';
				
				throw new $e(
					self::$aliases['error']($this->link).' - '.$queryString,
					$code
				);
			}
			
			return $result;
		}
		
		public function getTableInfo($table)
		{
			static $types = array(
				'tinyint'		=> DataType::SMALLINT,
				'smallint'		=> DataType::SMALLINT,
				'int'			=> DataType::INTEGER,
				'mediumint'		=> DataType::INTEGER,

				'bigint'		=> DataType::BIGINT,
				
				'double'		=> DataType::DOUBLE,
				'decimal'		=> DataType::NUMERIC,

				'char'			=> DataType::CHAR,
				'varchar'		=> DataType::VARCHAR,
				'text'			=> DataType::TEXT,
				'tinytext'		=> DataType::TEXT,
				'mediumtext'	=> DataType::TEXT,
				
				'date'			=> DataType::DATE,
				'time'			=> DataType::TIME,
				'timestamp'		=> DataType::TIMESTAMP,
				'datetime'		=> DataType::TIMESTAMP,

				// unhandled types
				'set'			=> null,
				'enum'			=> null,
				'year'			=> null
			);
			
			try {
				$result = $this->queryRaw('SHOW COLUMNS FROM '.$table);
			} catch (BaseException $e) {
				throw new ObjectNotFoundException(
					"unknown table '{$table}'"
				);
			}
			
			$table = new DBTable($table);
			
			while ($row = self::$aliases['fetch_assoc']($result)) {
				$name = strtolower($row['Field']);
				$matches = array();
				$info = array('type' => null, 'extra' => null);
				if (
					preg_match(
						'~(\w+)(\((\d+?)\)){0,1}\s*(\w*)~',
						strtolower($row['Type']),
						$matches
					)
				) {
					$info['type'] = $matches[1];
					$info['size'] = $matches[3];
					$info['extra'] = $matches[4];
				}
				
				Assert::isTrue(
					array_key_exists($info['type'], $types),
					
					'unknown type "'
					.$types[$info['type']]
					.'" found in column "'.$name.'"'
				);
				
				if (empty($types[$info['type']]))
					continue;
				
				$column = DBColumn::create(
					DataType::create($types[$info['type']])->
						setUnsigned(
							strtolower($info['extra']) == 'unsigned'
						)->
						setNull(strtolower($row['Null']) == 'yes'),
					
					$name
				)->
				setAutoincrement(strtolower($row['Extra']) == 'auto_increment')->
				setPrimaryKey(strtolower($row['Key']) == 'pri');
				
				if ($row['Default'])
					$column->setDefault($row['Default']);
				
				$table->addColumn($column);
			}
			
			return $table;
		}
		
		public function hasQueue()
		{
			return false;
		}
		
		protected function getInsertId()
		{
			return self::$aliases['insert_id']($this->link);
		}
		
		private function checkSingle($result)
		{
			if (self::$aliases['num_rows']($result) > 1)
				throw new TooManyRowsException(
					'query returned too many rows (we need only one)'
				);
			
			return $result;
		}
	}
?>