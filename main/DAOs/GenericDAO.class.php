<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Basis of all DAO's.
 *
 * @ingroup DAOs
 **/
abstract class GenericDAO extends Singleton implements BaseDAO
{
    protected $linkName = null;
    private $identityMap = [];

    public function makeObject($array, $prefix = null)
    {
        if (
        isset(
            $this->identityMap[$array[$idName = $prefix . $this->getIdName()]]
        )
        ) {
            $this->getProtoClass()->skipObjectPrefetching(
                $this->identityMap[$array[$idName]]
            );

            return $this->identityMap[$array[$idName]];
        }

        return
            $this->completeObject(
                $this->makeOnlyObject($array, $prefix)
            );
    }

    public function getIdName()
    {
        return 'id';
    }

    /**
     * @return AbstractProtoClass
     **/
    public function getProtoClass()
    {
        static $protos = [];

        if (!isset($protos[$className = $this->getObjectName()])) {
            $protos[$className] = call_user_func([$className, 'proto']);
        }

        return $protos[$className];
    }

    abstract public function getObjectName();

    public function completeObject(Identifiable $object)
    {
        return $this->getProtoClass()->completeObject(
        // same purpose as in makeOnlyObject,
        // but for objects retrieved from cache
            $this->addObjectToMap($object)
        );
    }

    private function addObjectToMap(Identifiable $object)
    {
        return $this->identityMap[$object->getId()] = $object;
    }

    public function makeOnlyObject($array, $prefix = null)
    {
        // adding incomplete object to identity map
        // solves case with circular-dependent objects
        return $this->addObjectToMap(
            $this->getProtoClass()->makeOnlyObject(
                $this->getObjectName(), $array, $prefix
            )
        );
    }

    /**
     * Returns link name which is used to get actual DB-link from DBPool,
     * returning null by default for single-source projects.
     *
     * @see DBPool
     **/
    public function getLinkName()
    {
        return $this->linkName;
    }

    public function getSequence()
    {
        return $this->getTable() . '_id';
    }

    abstract public function getTable();

    /**
     * @return SelectQuery
     **/
    public function makeSelectHead()
    {
        static $selectHead = [];

        if (!isset($selectHead[$className = $this->getObjectName()])) {
            $table = $this->getTable();

            $object =
                OSQL::select()->
                from($table);

            foreach ($this->getFields() as $field) {
                $object->get(new DBField($field, $table));
            }

            $selectHead[$className] = $object;
        }

        return clone $selectHead[$className];
    }

    public function getFields()
    {
        static $fields = [];

        $className = $this->getObjectName();

        if (!isset($fields[$className])) {
            $fields[$className] = array_values($this->getMapping());
        }

        return $fields[$className];
    }

    public function getMapping()
    {
        return $this->getProtoClass()->getMapping();
    }

    /// boring delegates
    //@{

    /**
     * @return SelectQuery
     **/
    public function makeTotalCountQuery()
    {
        return
            (new SelectQuery())
                ->get(
                    new SQLFunction('count', new DBValue('*'))
                )
                ->from($this->getTable());
    }

    public function getById($id, $expires = Cache::EXPIRES_MEDIUM)
    {
        Assert::isScalar($id);
        Assert::isNotEmpty($id);

        if (isset($this->identityMap[$id])) {
            return $this->identityMap[$id];
        }

        return $this->addObjectToMap(
            Cache::worker($this)->getById($id, $expires)
        );
    }

    public function getByLogic(
        LogicalObject $logic,
        $expires = Cache::DO_NOT_CACHE
    ) {
        return $this->addObjectToMap(
            Cache::worker($this)->getByLogic($logic, $expires)
        );
    }

    public function getByQuery(
        SelectQuery $query,
        $expires = Cache::DO_NOT_CACHE
    ) {
        return $this->addObjectToMap(
            Cache::worker($this)->getByQuery($query, $expires)
        );
    }

    public function getCustom(
        SelectQuery $query,
        $expires = Cache::DO_NOT_CACHE
    ) {
        return Cache::worker($this)->getCustom($query, $expires);
    }

    public function getListByIds(
        array $ids,
        $expires = Cache::EXPIRES_MEDIUM
    ) {
        $mapped = $remain = [];

        foreach ($ids as $id) {
            if (isset($this->identityMap[$id])) {
                $mapped[] = $this->identityMap[$id];
            } else {
                $remain[] = $id;
            }
        }

        if ($remain) {
            $list = $this->addObjectListToMap(
                Cache::worker($this)->getListByIds($remain, $expires)
            );

            $mapped = array_merge($mapped, $list);
        }

        return ArrayUtils::regularizeList($ids, $mapped);
    }

    private function addObjectListToMap($list)
    {
        foreach ($list as $object) {
            $this->identityMap[$object->getId()] = $object;
        }

        return $list;
    }

    public function getListByQuery(
        SelectQuery $query,
        $expires = Cache::DO_NOT_CACHE
    ) {
        return $this->addObjectListToMap(
            Cache::worker($this)->getListByQuery($query, $expires)
        );
    }

    public function getListByLogic(
        LogicalObject $logic,
        $expires = Cache::DO_NOT_CACHE
    ) {
        return $this->addObjectListToMap(
            Cache::worker($this)->getListByLogic($logic, $expires)
        );
    }

    public function getPlainList($expires = Cache::EXPIRES_MEDIUM)
    {
        return $this->addObjectListToMap(
            Cache::worker($this)->getPlainList($expires)
        );
    }

    public function getTotalCount($expires = Cache::DO_NOT_CACHE)
    {
        return Cache::worker($this)->getTotalCount($expires);
    }

    public function getCustomList(
        SelectQuery $query,
        $expires = Cache::DO_NOT_CACHE
    ) {
        return Cache::worker($this)->getCustomList($query, $expires);
    }

    public function getCustomRowList(
        SelectQuery $query,
        $expires = Cache::DO_NOT_CACHE
    ) {
        return Cache::worker($this)->getCustomRowList($query, $expires);
    }

    public function getQueryResult(
        SelectQuery $query,
        $expires = Cache::DO_NOT_CACHE
    ) {
        return Cache::worker($this)->getQueryResult($query, $expires);
    }

    public function drop(Identifiable $object)
    {
        $this->checkObjectType($object);

        return $this->dropById($object->getId());
    }

    protected function checkObjectType(Identifiable $object)
    {
        Assert::isSame(
            get_class($object),
            $this->getObjectName(),
            'strange object given, i can not inject it'
        );
    }

    public function dropById($id)
    {
        unset($this->identityMap[$id]);

        $count = Cache::worker($this)->dropById($id);

        if (1 != $count) {
            throw new WrongStateException('no object were dropped');
        }

        return $count;
    }

    public function dropByIds(array $ids)
    {
        foreach ($ids as $id) {
            unset($this->identityMap[$id]);
        }

        $count = Cache::worker($this)->dropByIds($ids);

        if ($count != count($ids)) {
            throw new WrongStateException('not all objects were dropped');
        }

        return $count;
    }

    public function uncacheById($id)
    {
        return $this->getUncacherById($id)->uncache();
    }
    //@}

    /**
     * @return UncachersPool
     */
    public function getUncacherById($id)
    {
        return  new UncacherGenericDAO($this, $id, Cache::worker($this)->getUncacherById($id));
    }

    public function uncacheByIds($ids)
    {
        if (empty($ids)) {
            return;
        }

        $uncacher = $this->getUncacherById(array_shift($ids));

        foreach ($ids as $id) {
            $uncacher->merge($this->getUncacherById($id));
        }

        return $uncacher->uncache();
    }

    public function uncacheLists()
    {
        $this->dropIdentityMap();

        return Cache::worker($this)->uncacheLists();
    }

    /**
     * @return GenericDAO
     **/
    public function dropIdentityMap()
    {
        $this->identityMap = [];

        return $this;
    }

    public function dropObjectIdentityMapById($id)
    {
        unset($this->identityMap[$id]);

        return $this;
    }

    /* void */

    public function registerWorkerUncacher(UncacherBase $uncacher)
    {
        DBPool::getByDao($this)->registerUncacher($uncacher);
    }

    protected function inject(
        InsertOrUpdateQuery $query,
        Identifiable $object
    ) {
        $this->checkObjectType($object);

        return $this->doInject(
            $this->setQueryFields(
                $query->setTable($this->getTable()), $object
            ),
            $object
        );
    }

    protected function doInject(
        InsertOrUpdateQuery $query,
        Identifiable $object
    ) {
        $db = DBPool::getByDao($this);

        if (!$db->isQueueActive()) {
            $preUncacher = is_scalar($object->getId())
                ? $this->getUncacherById($object->getId())
                : null;

            $count = $db->queryCount($query);

            $uncacher = $this->getUncacherById($object->getId());
            if ($preUncacher) {
                $uncacher->merge($uncacher);
            }
            $uncacher->uncache();

            if ($count !== 1) {
                throw new WrongStateException(
                    $count . ' rows affected: racy or insane inject happened: '
                    . $query->toDialectString($db->getDialect())
                );
            }
        } else {
            $preUncacher = is_scalar($object->getId())
                ? $this->getUncacherById($object->getId())
                : null;

            $db->queryNull($query);

            $uncacher = $this->getUncacherById($object->getId());
            if ($preUncacher) {
                $uncacher->merge($uncacher);
            }
            $uncacher->uncache();
        }

        // clean out Identifier, if any
        return $this->addObjectToMap($object->setId($object->getId()));
    }
}
