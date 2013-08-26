<?php
/**
 * DB Cursor implementation
 * @author Alex Gorbylev <alex@adonweb.ru>
 * @date 2013.04.25
 */
final class Cursor implements Iterator {

	/** @var ProtoDAO */
	protected $dao = null;

	/** @var SelectQuery */
	protected $selectQuery = null;

	/** @var DB */
	protected $db = null;

	/** @var string */
	protected $cursorName = null;

	/** @var array */
	protected $buffer = array();

	/** @var int */
	protected $batchSize = 250;

	/** @var bool */
	protected $iterateObjects = null;

	/** @var int */
	protected $iteratorPosition = 0;

	/** @var mixed */
	protected $iteratorCurrent = null;

	public static function create(ProtoDAO $dao, SelectQuery $query = null) {
		return new self($dao, $query);
	}

	public function __construct(ProtoDAO $dao, SelectQuery $query = null) {
		if ($query)
			Assert::isTrue($query instanceof SelectQuery);

		$this->dao = $dao;
		$this->db = DBPool::getByDao($this->dao);
		$this->selectQuery = $query;

		$this->openTransaction();
		$this->declareCursor();
	}

	function __destruct() {
		if( $this->db->inTransaction() && is_resource($this->db->getLink()) ) {
			$this->closeCursor();
			$this->closeTransaction();
		}
	}

	public function __clone()
	{
		$this->dao = clone $this->dao;
		$this->selectQuery = clone $this->selectQuery;
	}

	/**
	 * @return Cursor
	 */
	public function asObjects() {
		$this->iterateObjects = true;
		return $this;
	}

	/**
	 * @return Cursor
	 */
	public function asRows() {
		$this->iterateObjects = false;
		return $this;
	}

	public function setBatchSize($size) {
		if( Assert::checkInteger($size) && $size>0 ) {
			$this->batchSize = $size;
		}
		return $this;
	}

	/**
	 * @param bool $strict
	 * @throws ObjectNotFoundException
	 * @return Prototyped|bool
	 */
	public function getNext($strict=false) {
		$row = $this->fetchRow();
		if( !$row ) {
			if( $strict ) {
				throw new ObjectNotFoundException();
			}
			return false;
		}
		return $this->dao->getProtoClass()->makeOnlyObject($this->dao->getObjectName(), $row);
	}

	/**
	 * @param bool $strict
	 * @throws ObjectNotFoundException
	 * @return array|bool
	 */
	public function getNextRow($strict=false) {
		$row = $this->fetchRow();
		if( !$row ) {
			if( $strict ) {
				throw new ObjectNotFoundException();
			}
			return false;
		}
		return $row;
	}

	public function current() {
		return $this->iteratorCurrent;
	}

	public function key() {
		return $this->iteratorPosition;
	}

	public function next() {
		$this->iteratorPosition++;
	}

	public function valid() {
		$this->iteratorCurrent = $this->iterateObjects ? $this->getNext() : $this->getNextRow();
		return $this->iteratorCurrent===false ? false : true;
	}

	public function rewind() {
		if( is_null($this->iterateObjects) ) {
			throw new WrongStateException('Type of iterating objects is not defined, use asObjects() or asRows()');
		}
		$this->iteratorPosition = 0;
	}

	public function close() {
		$this->closeCursor();
		$this->closeTransaction();
		$this->cursorName = null;
	}


	/**
	 * @return SelectQuery
	 */
	protected function getSelectQuery() {
		if( is_null($this->selectQuery) ) {
			$this->selectQuery = $this->dao->makeSelectHead();
		}
		return $this->selectQuery;

	}

	/**
	 * @return string
	 */
	protected function getCursorName() {
		if( is_null($this->cursorName) ) {
			$this->cursorName = 'cursor_'.dechex(crc32(time().$this->dao->getTable()));
		}
		return $this->cursorName;
	}

	protected function openTransaction() {
		$this->db->begin();
	}

	protected function declareCursor() {
		$queryDeclare = 'DECLARE '.$this->getCursorName().' CURSOR FOR '.$this->getSelectQuery()->toDialectString($this->db->getDialect());
		$this->db->queryRaw($queryDeclare);
	}

	protected function fetchRow() {
		if( empty($this->buffer) ) {
			$resource = $this->db->queryRaw('FETCH FORWARD 250 FROM '.$this->getCursorName());
			$this->buffer = pg_fetch_all($resource);
		}
		if( !$this->buffer ) {
			return false;
		}
		return array_shift($this->buffer);
	}

	protected function closeCursor() {
		$queryOpen = 'CLOSE '.$this->getCursorName();
		$this->db->queryRaw($queryOpen);
	}

	protected function closeTransaction() {
		$this->db->commit();
	}

	final private function __sleep() {/* restless class */}
	final private function __wakeup() {/* restless class */}
}