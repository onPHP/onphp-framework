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
						->find( array('$or' => $this->makeIdList($keys)) );
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

	public function getListByField($table, $field, $value, Criteria $criteria = null) {
		if( Assert::checkInteger($value) ) {
			$value = (int)$value;
		}
		// quering
		$cursor =
			$this
				->db
					->selectCollection($table)
						->find( array($field => $value) );
		// criteria
		if( !is_null($criteria) ) {
			if( $limit = $criteria->getLimit() ) {
				$cursor->limit( $limit );
			}
			if( $offset = $criteria->getOffset() ) {
				$cursor->skip( $offset );
			}
			if( $order = $criteria->getOrder()->getLast() ) {
				$cursor->sort( array($order->getFieldName() => $order->isAsc()?1:-1) );
			}
		}

		// recieving objects
		$rows = array();
		foreach ($cursor as $row) {
			$rows[] = $this->decodeId($row);
		}
		// return result
		return $rows;
	}

	public function getIdListByField($table, $field, $value, Criteria $criteria = null) {
		if( Assert::checkInteger($value) ) {
			$value = (int)$value;
		}
		// quering
		$cursor =
			$this
				->db
					->selectCollection($table)
						->find( array($field => $value), array('_id') );
		// criteria
		if( !is_null($criteria) ) {
			if( $criteria->getLimit() ) {
				$cursor->limit( $criteria->getLimit() );
			}
			if( $criteria->getOffset() ) {
				$cursor->skip( $criteria->getOffset() );
			}
			if( $criteria->getOrder() ) {
				/** @var $order OrderBy */
				$order = $criteria->getOrder()->getLast();
				$cursor->sort( array($order->getFieldName() => $order->isAsc()?1:-1) );
			}
		}

		// recieving objects
		$rows = array();
		foreach ($cursor as $row) {
			$rows[] = $this->decodeId($row);
		}
		// return result
		return $rows;
	}

	public function find($table, $query) {
		// quering
		$cursor =
			$this
				->db
					->selectCollection($table)
						->find( $query );
		// recieving objects
		$rows = array();
		foreach ($cursor as $row) {
			$rows[] = $this->decodeId($row);
		}
		// return result
		return $rows;
	}

/// helper functions
//@{
	/**
	 * Encode ID to MongoId
	 * @param array $row
	 * @return array
	 */
	protected function encodeId(array $row) {
		$row['_id'] = $this->makeId($row['id']);
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
			$fields[] = array( '_id'=>$this->makeId($key) );
		}
		return $fields;
	}

//	protected function prepareQuery($terms, $unite) {
//		$query = array();
//		foreach( $terms as $key=>$value ) {
//			$query[$key] = $value;
//		}
//		// query type check
//		if( !$unite ) {
//			$query = array('$or'=>$query );
//		}
//		return $query;
//	}
//@}
}
