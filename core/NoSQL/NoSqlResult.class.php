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

	/** @var Criteria */
	protected $criteria;

	protected $count = null;

	/**
	 * @static
	 * @param MongoCursor $cursor
	 * @return NoSqlResult
	 */
	public static function create() {
		return new static;
	}


	public function setCriteria(Criteria $criteria) {
		$this->criteria = $criteria;
		return $this;
	}

	public function getCriteria() {
		return $this->criteria;
	}

	public function getCount() {
		if ($this->count == null && $this->getMongoCursor()) {
			$count = $this->getMongoCursor()->count();
			MongoBase::assertCountResult($count);

			$this->count = $count;

			/* -- плохой вариант, долго считает
			// пытаемся посчитать количество записей перебором, без использования count()
			// откидиываем limit и offset
			$criteria = clone $this->getCriteria();
			$criteria
				->setLimit(null)
				->setOffset(null);
			// находим в логике NoSQLExpression
			$expression = null;
			foreach ($criteria->getLogic()->getChain() as $logic) {
				if ($logic instanceof NoSQLExpression) {
					$expression = $logic;
				}
			}

			// если нашли - делаем запрос
			if ($expression instanceof NoSQLExpression) {
				// берем первое попавшееся в запросе поле и запрашиваем только его
				foreach ($expression->toMongoQuery() as $field => $condition) {
					if ($field[0] != '$') {
						$expression->addField(array($field));
						break;
					}
				}

				$timeStart = microtime(1);
				$timeMax = Config::me()->getMongoTimeout() / 2;
				$this->count = 0;
				$db = NoSqlPool::me()->getByDao($this->getDao());
				$cursor = $db->makeCursorByCriteria($criteria);
				// пересчитываем количество выбранных записей
				foreach ($cursor as $item) {
					$this->count++;

					// следим за таймаутом, т.к. время запроса к базе не учитывается
					// в max_execution_time
					if (microtime(1) - $timeStart > $timeMax) {
						throw new MongoCursorTimeoutException(
							'failed to count in ' . $timeMax . ' seconds: '
							. json_encode($expression->toMongoQuery())
						);
					}
				}

			} else {
				$this->count = $this->getMongoCursor()->count();
			}
			*/
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
