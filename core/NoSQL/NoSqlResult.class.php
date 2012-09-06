<?php
/**
 *
 * @author Михаил Кулаковский <m@klkvsk.ru>
 * @date 2012.07.02
 */
class NoSqlResult extends QueryResult {
	/** @var NoSqlDAO */
	protected $dao;

	/** @var MongoCursor */
	protected $mongoCursor;

	/** @var NoSqlResultList */
	protected $resultList;

	protected $count = null;

	/**
	 * @static
	 * @param MongoCursor $cursor
	 * @return NoSqlResult
	 */
	public static function create() {
		return new static;
	}

	public function getCount() {
		if ($this->count == null && $this->getMongoCursor()) {
			$this->count = $this->getMongoCursor()->count();
		}
		return $this->count;
	}


	/**
	 * @return NoSqlResultList
	 */
	public function getList() {
		return $this->resultList;
	}

	/**
	 * @param $resultList
	 * @return NoSqlResult
	 * @throws WrongArgumentException
	 */
	public function setList($resultList) {
		if (!($resultList instanceof NoSqlResultList)) {
			throw new WrongArgumentException('NoSqlResult accepts only NoSqlResultList in setList');
		}
		$this->resultList = $resultList;
		return $this;
	}

	/**
	 * @param MongoCursor $cursor
	 * @return NoSqlResult
	 */
	public function setMongoCursor(MongoCursor $cursor) {
		$this->mongoCursor = $cursor;
		$this
			->setList(NoSqlResultList::create($this))
			->setCount(null); // lazy
		return $this;
	}

	/**
	 * @return MongoCursor
	 */
	public function getMongoCursor() {
		return $this->mongoCursor;
	}

	/**
	 * @param NoSqlDAO $dao
	 * @return NoSqlResult
	 */
	public function setDao(NoSqlDAO $dao) {
		$this->dao = $dao;
		return $this;
	}

	/**
	 * @return NoSqlDAO
	 */
	public function getDao() {
		return $this->dao;
	}

	/**
	 * @return null|string
	 */
	public function getId() {
		if ($this->getMongoCursor() == null) {
			return null;
		}
		$queryInfo = $this->getMongoCursor()->info();
		return '_result_nosql_' . sha1(json_encode($queryInfo['query']));
	}

	public function setQuery(SelectQuery $query) {
		throw new UnimplementedFeatureException('NoSqlResult has no SelectQuery');
	}

	public function getQuery() {
		throw new UnimplementedFeatureException('NoSqlResult has no SelectQuery');
	}

	public function setLimit($limit) {
		Assert::isInteger($limit);
		$this->getMongoCursor()->limit($limit);
		return $this;
	}

	public function setOffset($offset) {
		Assert::isInteger($offset);
		$this->getMongoCursor()->skip($offset);
		return $this;
	}
}
