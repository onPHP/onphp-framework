<?php
/**
 *
 * @author Михаил Кулаковский <m@klkvsk.ru>
 * @date 2012.07.02
 */
class NoSqlResultList implements Iterator {
	/** @var NoSqlResult */
	protected $result;

	/**
	 * @param NoSqlResult $result
	 */
	protected function __construct(NoSqlResult $result) {
		$this->result = $result;
	}

	/**
	 * @param NoSqlResult $result
	 * @return NoSqlResultList
	 */
	public static function create(NoSqlResult $result) {
		return new static($result);
	}

	public function getResult() {
		return $this->result;
	}

	public function getCursor() {
		return $this->getResult()->getMongoCursor();
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.cur;rent.php
	 * @return mixed Can return any type.
	 */
	public function current() {
		$row = $this->getCursor()->current();
		if ($row['_id'] instanceof MongoId) {
            $row['id'] = (string)$row['_id'];
        } else {
            $row['id'] = $row['_id'];
        }
		unset($row['_id']);
		return $this->result->getDao()->makeNoSqlObject($row);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Rewind the Iterator to the first element
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind() {
		$this->getCursor()->rewind();
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Checks if current position is valid
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid() {
		return $this->getCursor()->valid();
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the key of the current element
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return scalar scalar on success, or null on failure.
	 */
	public function key() {
		return $this->getCursor()->key();
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Move forward to next element
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next() {
		$this->getCursor()->next();
	}

}
