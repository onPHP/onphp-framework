<?php
/**
 * MongoBase connector.
 *
 * @see http://www.mongodb.org/
 *
 * @ingroup NoSQL
 * @author Alex Gorbylev <alex@gorbylev.ru>
 * @date 2012.03.27
 */
class MongoBase extends NoSQL {

	const C_TABLE	= 1001;
	const C_QUERY	= 1002;
	const C_ORDER	= 1003;
	const C_LIMIT	= 1004;
	const C_SKIP	= 1005;

	/**
	 * @var Mongo
	 */
	protected $link			= null;

	/**
	 * @var MongoDB
	 */
	protected $db			= null;

	/**
	 * @return MongoDB
	 * @throws NoSQLException
	 */
	public function connect() {
		$conn =
			'mongodb://'
			.($this->username && $this->password ? "{$this->username}:{$this->password}@" : null)
			.$this->hostname
			.($this->port ? ":{$this->port}" : null);

		$options = array("connect" => true);

		if ($this->persistent) {
			$options['persist'] = $this->hostname.'-'.$this->basename;
		}
		try {
			$this->link = new Mongo($conn, $options);
			$this->db = $this->link->selectDB($this->basename);
		} catch(MongoConnectionException $e) {
			throw new NoSQLException(
				'can not connect to MongoBase server: '.$e->getMessage()
			);
		} catch(InvalidArgumentException $e) {
			throw new NoSQLException(
				'can not select DB in MongoBase: '.$e->getMessage()
			);
		}

		return $this;
	}

	/**
	 * @return MongoDB
	 */
	public function disconnect() {
		if( $this->isConnected() ) {
			$this->link->close();
		}
		$this->link = null;
		$this->db = null;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isConnected() {
		return ($this->link instanceof Mongo && $this->link->connected);
	}

	/**
	 * @param string $sequence
	 * @return MongoId
	 */
	public function obtainSequence($sequence) {
		return new MongoId(mb_strtolower(trim($sequence)));
	}

	public function selectOne($table, $key) {
		$row =
			$this
				->db
					->selectCollection($table)
						->findOne( array('_id' => new MongoId($key)) );
		if( is_null($row) ) {
			throw new ObjectNotFoundException( 'Object with id "'.$key.'" in table "'.$table.'" not found!' );
		}
		// return clean row
		return $this->decodeId($row);
	}

	public function selectList($table, array $keys) {
		// quering
		$cursor =
			$this
				->db
					->selectCollection($table)
						->find( array('_id' => array('$in'=>$this->makeIdList($keys)) ) );
		// recieving objects
		$rows = array();
		foreach ($cursor as $row) {
			$rows[] = $this->decodeId($row);
		}
		// return result
		return $rows;
	}

	public function insert($table, array $row) {
		$row = $this->encodeId($row);
		// save
		$result =
			$this
				->db
					->selectCollection($table)
						->insert($row);
		// checking result
		if( !$result ) {
			throw new NoSQLException('Could not insert object: '.var_export($row, true));
		}
		// return clean row
		return $this->decodeId($row);
	}

	public function batchInsert($table, array $rows) {
		throw new UnimplementedFeatureException('Unfortunately method "batchInsert" is not implemented yet :(');
	}

	public function update($table, array $row) {
		$row = $this->encodeId($row);
		$id = $row['_id'];
		unset($row['_id']);

		$result =
			$this
				->db
					->selectCollection($table)
						->update(array('_id' => $id), $row);
		// checking result
		if( !$result ) {
			throw new NoSQLException('Could not update object: '.var_export($row, true));
		}
		$row['_id'] = $id;
		// return clean row
		return $this->decodeId($row);
	}

	public function deleteOne($table, $key) {
		return
			$this
				->db
					->selectCollection($table)
						->remove( array('_id' => $this->makeId($key)), array('justOne' => true) );
	}

	public function deleteList($table, array $keys) {
		return
			$this
				->db
					->selectCollection($table)
						->remove( array('$or' => $this->makeIdList($keys)) );
	}

	public function getPlainList($table) {
		// quering
		$cursor =
			$this
				->db
					->selectCollection($table)
						->find();
		// recieving objects
		$rows = array();
		foreach ($cursor as $row) {
			$rows[] = $this->decodeId($row);
		}
		// return result
		return $rows;
	}

	public function getTotalCount($table) {
		return
			$this
				->db
					->selectCollection($table)
						->find()
							->count();
	}

	public function getCountByField($table, $field, $value, Criteria $criteria = null) {
		if( Assert::checkInteger($value) ) {
			$value = (int)$value;
		}
		$options = $this->parseCriteria($criteria);

		return
			$this->mongoCount($table, array($field => $value), array('_id'), $options[self::C_ORDER], $options[self::C_LIMIT], $options[self::C_SKIP]);
	}

	public function getListByField($table, $field, $value, Criteria $criteria = null) {
		if( Assert::checkInteger($value) ) {
			$value = (int)$value;
		}
		$options = $this->parseCriteria($criteria);

		return
			$this->mongoFind($table, array($field => $value), array(), $options[self::C_ORDER], $options[self::C_LIMIT], $options[self::C_SKIP]);
	}

	public function getIdListByField($table, $field, $value, Criteria $criteria = null) {
		if( Assert::checkInteger($value) ) {
			$value = (int)$value;
		}
		$options = $this->parseCriteria($criteria);

		return
			$this->mongoFind($table, array($field => $value), array('_id'), $options[self::C_ORDER], $options[self::C_LIMIT], $options[self::C_SKIP]);
	}

	public function find($table, $query) {
		return
			$this->mongoFind($table, $query);
	}

	public function findByCriteria(Criteria $criteria) {
		$options = $this->parseCriteria($criteria);

		if( !isset($options[self::C_TABLE]) ) {
			throw new NoSQLException('Can not find without table!');
		}
//		if( !isset($options[self::C_QUERY]) ) {
//			throw new NoSQLException('Can not find without query!');
//		}

		return
			$this->mongoFind($options[self::C_TABLE], $options[self::C_QUERY], array(), $options[self::C_ORDER], $options[self::C_LIMIT], $options[self::C_SKIP]);
	}

	public function countByCriteria(Criteria $criteria) {
		$options = $this->parseCriteria($criteria);

		if( !isset($options[self::C_TABLE]) ) {
			throw new NoSQLException('Can not find without table!');
		}
//		if( !isset($options[self::C_QUERY]) ) {
//			throw new NoSQLException('Can not find without query!');
//		}

		return
			$this->mongoCount($options[self::C_TABLE], $options[self::C_QUERY], array(), $options[self::C_ORDER], $options[self::C_LIMIT], $options[self::C_SKIP]);
	}

	protected function mongoFind($table, array $query, array $fields=array(), array $order=null, $limit=null, $skip=null) {
		// quering
		$cursor = $this->mongoMakeCursor($table, $query, $fields, $order, $limit, $skip);
		// recieving objects
		$rows = array();
		foreach ($cursor as $row) {
			$rows[] = $this->decodeId($row);
		}
		// return result
		return $rows;
	}

	protected function mongoCount($table, array $query, array $fields=array(), array $order=null, $limit=null, $skip=null) {
		// quering
		$cursor = $this->mongoMakeCursor($table, $query, $fields, $order, $limit, $skip);
		// return result
		return $cursor->count();
	}

	/**
	 * @param $table
	 * @param array $query
	 * @param array $fields
	 * @param array $order
	 * @param int $limit
	 * @param int $skip
	 * @return MongoCursor
	 */
	protected function mongoMakeCursor($table, array $query, array $fields=array(), array $order=null, $limit=null, $skip=null) {
		$cursor =
			$this
				->db
					->selectCollection($table)
						->find( $query, $fields );
		if( !is_null($order) ) {
			$cursor->sort( $order );
		}
		if( !is_null($limit) ) {
			$cursor->limit( $limit );
		}
		if( !is_null($skip) ) {
			$cursor->skip( $skip );
		}
		return $cursor;
	}

	/**
	 * @param string   $table
	 * @param string   $map
	 * @param string   $reduce
	 * @param Criteria $criteria
	 * @param int      $timeout
	 * @throws NoSQLException
	 * @return array
	 */
	public function mapReduce($table, $map, $reduce, Criteria $criteria=null, $timeout=30) {
		$options = $this->parseCriteria($criteria);

		$command = array(
			'mapreduce'	=> $table,
			'map'		=> new MongoCode($map),
			'reduce'	=> new MongoCode($reduce),
			'out'		=> array('inline' => 1)
		);
		// обрабатываем критерию
		if( !empty($options[self::C_QUERY]) ) {
			$command['query'] = $options[self::C_QUERY];
		}
		if( !empty($options[self::C_ORDER]) ) {
			$command['sort'] = $options[self::C_ORDER];
		}
		if( !empty($options[self::C_LIMIT]) ) {
			$command['limit'] = $options[self::C_LIMIT];
		}

		$result = $this->db->command($command, array('timeout'=>$timeout*1000));

		// обрабатываем результаты
		$list = array();
		if( is_array($result) && isset($result['ok']) && $result['ok']==1 ) {
			foreach( $result['results'] as $row ) {
				// prepare id
				$row['id'] = $row['_id'];
				unset($row['_id']);
				// prepare values
				foreach($row['value'] as $key=>$value) {
					$row[$key] = is_bool($value) ? (int)$value : $value;
				}
				unset($row['value']);

				$list[ $row['id'] ] = $row;
			}
		} else {
			throw new NoSQLException('Error during map/reduce running');
		}
		return $list;
	}

/// helper functions
//@{
	/**
	 * Encode ID to MongoId
	 * @param array $row
	 * @return array
	 */
	protected function encodeId(array $row) {
		if( isset($row['id']) ) {
			$row['_id'] = $this->makeId($row['id']);
		}
		unset($row['id']);
		return $row;
	}

	/**
	 * Decode ID from MongoId to string
	 * @param array $row
	 * @return array
	 */
	protected function decodeId(array $row) {
		$row['id'] = (string)$row['_id'];
		unset($row['_id']);
		return $row;
	}

	protected function makeId($key) {
		return ($key instanceof MongoId) ? $key : new MongoId($key);
	}

	protected function makeIdList(array $keys) {
		$fields = array();
		foreach( $keys as $key ) {
			//$fields[] = array( '_id'=>$this->makeId($key) );
			$fields[] = $this->makeId($key);
		}
		return $fields;
	}

	/**
	 * Разбираем критерию на параметры запроса к монго
	 * @param Criteria $criteria
	 * @return array
	 */
	protected function parseCriteria(Criteria $criteria=null) {
		$result = array();
		// парсим табличку
		if( !is_null($criteria) && $criteria->getDao() ) {
			$result[self::C_TABLE] = $criteria->getDao()->getTable();
		} else {
			$result[self::C_TABLE] = null;
		}
		// парсим запросы
		if( !is_null($criteria) && $criteria->getLogic()->getLogic() ) {
			$logic = $criteria->getLogic()->getChain();
			$expression = array_shift($logic);
			if( $expression instanceof NoSQLExpression ) {
				$result[self::C_QUERY] = $expression->toMongoQuery();
			} else {
				$result[self::C_QUERY] = array();
			}
		} else {
			$result[self::C_QUERY] = array();
		}
		// парсим сортировку
		if( !is_null($criteria) && $criteria->getOrder() ) {
			/** @var $order OrderBy */
			$order = $criteria->getOrder()->getLast();
			if( $order instanceof OrderBy ) {
				$result[self::C_ORDER] = array($order->getFieldName() => $order->isAsc()?1:-1);
			} else {
				$result[self::C_ORDER] = null;
			}
		} else {
			$result[self::C_ORDER] = null;
		}
		// парсим лимит
		if( !is_null($criteria) && $criteria->getLimit() ) {
			$result[self::C_LIMIT] = $criteria->getLimit();
		} else {
			$result[self::C_LIMIT] = null;
		}
		// парсим сдвиг
		if( !is_null($criteria) && $criteria->getOffset() ) {
			$result[self::C_SKIP] = $criteria->getOffset();
		} else {
			$result[self::C_SKIP] = null;
		}
		// отдаем результат
		return $result;
	}

//@}
}
