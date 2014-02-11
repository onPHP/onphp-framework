<?php
/**
 * Created by PhpStorm.
 * User: byorty
 * Date: 31.12.13
 * Time: 21:18
 */

class LinkedDaoWorker extends TrickyDaoWorker {

	const
		SUFFIX_ITEM = '_item_',
		SUFFIX_CUSTOM = '_custom_'
	;

	private $suffix;

	public function getById($id, $expires = Cache::EXPIRES_MEDIUM) {
		return $this->getByQuery($this->makeIdKey($id, false), $expires);
	}

	protected function makeIdKey($id, $toString = true) {
		/** @var SelectQuery $query */
		$query =
			$this->dao->
				makeSelectHead()->
				andWhere(
					Expression::eq(
						DBField::create(
							$this->dao->getIdName(),
							$this->dao->getTable()
						),
						$id
					)
				);
		if ($toString) {
			return $this->makeQueryKey($query, self::SUFFIX_ITEM);
		} else {
			return $query;
		}
	}

	/**
	 * @return $this
	 */
	private function setSuffixItem() {
		$this->suffix = self::SUFFIX_ITEM;
		return $this;
	}

	/**
	 * @return $this
	 */
	private function setSuffixList() {
		$this->suffix = self::SUFFIX_LIST;
		return $this;
	}

	/**
	 * @return $this
	 */
	private function setSuffixCustom() {
		$this->suffix = self::SUFFIX_CUSTOM;
		return $this;
	}

	protected function getSuffixQuery() {
		return $this->suffix;
	}

	public function getByQuery(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
		$this->setSuffixItem();
		return parent::getByQuery($query, $expires);
	}

	public function getCustom(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
		$this->setSuffixCustom();
		return parent::getCustom($query, $expires);
	}

	public function getListByQuery(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
		$this->setSuffixList();
		return parent::getListByQuery($query, $expires);
	}

	public function getCustomList(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
		$this->setSuffixCustom();
		return parent::getCustomList($query, $expires);
	}

	public function getCustomRowList(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
		$this->setSuffixCustom();
		return parent::getCustomRowList($query, $expires);
	}

	public function getQueryResult(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
		$this->setSuffixCustom();
		return parent::getQueryResult($query, $expires);
	}

	public function uncacheByIds($ids) {
		$keys = array();
		foreach ($ids as $id) {
			$keys[] = $this->makeIdKey($id);
		}

		$isUncacheIds = Cache::me()
			->mark($this->className)
			->deleteList($keys)
		;

		return $isUncacheIds && $this->dao->uncacheLists();
	}

	public function uncacheLists() {
		return Cache::me()
			->mark($this->className)
			->deleteByPattern($this->className . self::SUFFIX_LIST)
			;
	}

	public function uncacheItems() {
		return Cache::me()
			->mark($this->className)
			->deleteByPattern($this->className . self::SUFFIX_ITEM)
			;
	}

    protected function cacheByQuery(
        SelectQuery $query,
        /* Identifiable */ $object,
        $expires = Cache::DO_NOT_CACHE
    )
    {
        if ($expires !== Cache::DO_NOT_CACHE) {

            if (self::SUFFIX_ITEM == $this->getSuffixQuery()) {

                $idKey = $this->makeIdKey($object->getId());
                $queryKey = $this->makeQueryKey($query, $this->getSuffixQuery());

                if ($idKey != $queryKey) {
                    Cache::me()
                        ->mark($this->className)
                        ->add(
                            $queryKey,
                            CacheLink::create()
                                ->setKey($idKey),
                            $expires
                        );
                }

                Cache::me()
                    ->mark($this->className)
                    ->add(
                        $idKey,
                        $object,
                        $expires
                    );
            } else if (self::SUFFIX_LIST == $this->getSuffixQuery()) {
                /** @var CacheListLink $link */
                $link = CacheListLink::create();
                foreach ($object as $item) {
                    $idKey = $this->makeIdKey($item->getId());

                    Cache::me()
                        ->mark($this->className)
                        ->add(
                            $idKey,
                            $item,
                            $expires
                        );

                    $link->setKey($item->getId(), $idKey);
                }

                parent::cacheByQuery($query, $link, $expires);
            } else {
                parent::cacheByQuery($query, $object, $expires);
            }
        }

        return $object;
    }

    protected function getCachedByQuery(SelectQuery $query)
    {
        $object = Cache::me()
            ->mark($this->className)
            ->get(
                $this->makeQueryKey(
                    $query,
                    $this->getSuffixQuery()
                )
            )
        ;

        if ($object instanceof CacheLink) {
            $object = Cache::me()->get($object->getKey());
        } else if ($object instanceof CacheListLink) {

            $keys = $object->getKeys();
            $object = Cache::me()->getList($keys);

			foreach ($keys as $id => $key) {
                if (!$object[$key]) {
                    try {
                        $item = $this->dao->getById($id);
                        $object[$key] = $item;
                        Cache::me()
                            ->mark($this->className)
                            ->add(
                                $this->makeIdKey($id),
                                $item,
                                Cache::EXPIRES_MEDIUM
                            );
                    } catch (ObjectNotFoundException $e) {
                        unset($object[$key]);
                    }
                }
            }

			$object = array_values($object);
        }

        return $object;
    }
}