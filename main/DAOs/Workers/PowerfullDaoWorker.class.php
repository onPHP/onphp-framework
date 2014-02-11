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
}