<?php
/**
 * Created by PhpStorm.
 * User: byorty
 * Date: 12.12.13
 * Time: 6:51
 */

class PowerfullDaoWorker extends TrickyDaoWorker {

    const
        SUFFIX_ITEM = '_item_',
        SUFFIX_CUSTOM = '_custom_'
    ;

    private $suffix;
    private $custom;

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
            $this->setCustomOff();
            return $this->makeQueryKey($query, self::SUFFIX_ITEM);
        } else {
            return $query;
        }
    }

    /**
     * @return $this
     */
    private function setSuffixItemOn() {
        $this->suffix = self::SUFFIX_ITEM;
        return $this;
    }

    /**
     * @return $this
     */
    private function setSuffixListOn() {
        $this->suffix = self::SUFFIX_LIST;
        return $this;
    }

    /**
     * @return $this
     */
    private function setSuffixCustomOn() {
        $this->suffix = self::SUFFIX_CUSTOM;
        return $this;
    }

    /**
     * @return $this
     */
    private function setCustomOn() {
        $this->custom = true;
        return $this;
    }

    /**
     * @return $this
     */
    private function setCustomOff() {
        $this->custom = false;
        return $this;
    }

    protected function getSuffixQuery() {
        return $this->suffix;
    }

    protected function makeQueryKey(SelectQuery $query, $suffix) {
        if ($this->custom) {
            return parent::makeQueryKey($query, $suffix);
        } else {
            /** @var Dialect $dialect */
            $dialect = DBPool::getByDao($this->dao)->getDialect();
            $key = str_replace(
                ' ',
                '_',
                $this->className .
                $suffix .
                $query->whereToString($dialect) .
                $query->orderToString($dialect) .
                $query->limitTotString($dialect) .
                $query->offsetToString($dialect)
            );
            return $key;
        }
    }

    public function getByQuery(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
        $this
            ->setSuffixItemOn()
            ->setCustomOff()
        ;
        return parent::getByQuery($query, $expires);
    }

    public function getCustom(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
        $this
            ->setCustomOn()
            ->setSuffixCustomOn()
        ;
        return parent::getCustom($query, $expires);
    }

    public function getListByIds(array $ids, $expires = Cache::EXPIRES_MEDIUM) {

        $list = array();

        // dupes, if any, will be resolved later @ ArrayUtils::regularizeList
        $ids = array_unique($ids);

        if ($expires !== Cache::DO_NOT_CACHE) {
            $toFetch = array();
            $prefixed = array();

            foreach ($ids as $id)
                $prefixed[$id] = $this->makeIdKey($id);

            if ($cachedList = Cache::me()->mark($this->className)->getList($prefixed)) {
                $proto = $this->dao->getProtoClass();

                $proto->beginPrefetch();

                foreach ($cachedList as $cached) {
                    if ($cached && ($cached !== Cache::NOT_FOUND)) {
                        $list[] = $this->dao->completeObject($cached);

                        unset($prefixed[$cached->getId()]);
                    }
                }

                $proto->endPrefetch($list);
            }

            $toFetch += array_keys($prefixed);

            if ($toFetch) {
                try {

                    $fetchedList = $this->getListByLogic(
                        Expression::in(
                            new DBField(
                                $this->dao->getIdName(),
                                $this->dao->getTable()
                            ),
                            $toFetch
                        ),
                        Cache::DO_NOT_CACHE
                    );

                    $this
                        ->setCustomOn()
                        ->setSuffixItemOn()
                    ;

                    foreach ($fetchedList as $item) {
                        $this->cacheByQuery($this->makeIdKey($item->getId(), false), $expires);
                    }

                    $list = array_merge($list, $fetchedList);
                } catch (ObjectNotFoundException $e) {}
            }
        } elseif (count($ids)) {
            try {
                $list =
                    $this->getListByLogic(
                        Expression::in(
                            new DBField(
                                $this->dao->getIdName(),
                                $this->dao->getTable()
                            ),
                            $ids
                        ),
                        Cache::DO_NOT_CACHE
                    );
            } catch (ObjectNotFoundException $e) {/*_*/}
        }

        return $list;
    }


    public function getListByQuery(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
        $this
            ->setSuffixListOn()
            ->setCustomOff()
        ;
        return parent::getListByQuery($query, $expires);
    }

    public function getCustomList(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
        $this
            ->setSuffixCustomOn()
            ->setCustomOn()
        ;
        return parent::getCustomList($query, $expires);
    }

    public function getCustomRowList(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
        $this
            ->setSuffixCustomOn()
            ->setCustomOn()
        ;
        return parent::getCustomRowList($query, $expires);
    }

    public function getQueryResult(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
        $this
            ->setSuffixCustomOn()
            ->setCustomOn()
        ;
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
            ->deleteByPattern(self::SUFFIX_LIST)
        ;
    }

    public function uncacheItems() {
        return Cache::me()
            ->mark($this->className)
            ->deleteByPattern(self::SUFFIX_ITEM)
        ;
    }
}