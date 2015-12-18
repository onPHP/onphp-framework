<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Helpers
 **/
abstract class AbstractProtoClass extends Singleton
{
    private $depth = 0;
    private $storage = array();
    private $skipList = array();

    abstract protected function makePropertyList();

    /**
     * @return AbstractProtoClass
     **/
    public function beginPrefetch()
    {
        $this->storage[++$this->depth] = array();
        $this->skipList[$this->depth] = array();

        return $this;
    }

    /**
     * @return AbstractProtoClass
     **/
    public function skipObjectPrefetching(Identifiable $object)
    {
        if ($this->depth) {
            if (!isset($this->skipList[$this->depth][$object->getId()]))
                $this->skipList[$this->depth][$object->getId()] = 1;
            else
                ++$this->skipList[$this->depth][$object->getId()];
        }

        return $this;
    }

    public function endPrefetch(array $objectList)
    {
        if (!$this->depth)
            throw new WrongStateException('prefetch mode is already off');

        foreach ($this->storage[$this->depth] as $setter => $innerList) {
            Assert::isEqual(
                count($objectList),
                count($innerList) + array_sum($this->skipList[$this->depth])
            );

            $ids = array();

            foreach ($innerList as $inner)
                if ($inner)
                    $ids[] = $inner->getId();

            // finding first available inner object
            foreach ($innerList as $inner)
                if ($inner)
                    break;

            if (!$inner)
                continue;

            // put yet unmapped objects into dao's identityMap
            $inner->dao()->getListByIds($ids);

            $skippedMap = $this->skipList[$this->depth];

            $i = $j = 0;

            foreach ($objectList as $object) {
                $objectId = $object->getId();

                if (isset($skippedMap[$objectId])) {
                    if ($skippedMap[$objectId] == 1)
                        unset($skippedMap[$objectId]);
                    else
                        --$skippedMap[$objectId];
                    ++$j;
                    continue;
                }

                if ($innerList[$i]) {
                    try {
                        // avoid dao "caching" here
                        // because of possible breakage
                        // in overriden properties
                        $object->$setter(
                            $innerList[$i]->dao()->getById(
                                $innerList[$i]->getId()
                            )
                        );
                    } catch (ObjectNotFoundException $e) {
                        throw new WrongStateException(
                            'possible corruption found: ' . $e->getMessage()
                        );
                    }
                }

                ++$i;
            }

            Assert::isEqual(
                $i,
                count($objectList) - $j
            );
        }

        unset($this->skipList[$this->depth], $this->storage[$this->depth--]);

        return $objectList;
    }

    public static function makeOnlyObject($className, $array, $prefix = null, ProtoDAO $parentDao = null)
    {
        return self::assemblyObject(new $className, $array, $prefix, $parentDao);
    }

    public static function completeObject(Prototyped $object)
    {
        return self::fetchEncapsulants($object);
    }

    final public function getPropertyList()
    {
        static $lists = array();

        $className = get_class($this);

        if (!isset($lists[$className])) {
            $lists[$className] = $this->makePropertyList();
        }

        return $lists[$className];
    }

    final public function getExpandedPropertyList($prefix = null)
    {
        static $lists = array();

        $className = get_class($this);

        if (!isset($lists[$className])) {
            foreach ($this->makePropertyList() as $property) {
                if ($property instanceof InnerMetaProperty) {
                    $lists[$className] =
                        array_merge(
                            $lists[$className],
                            $property->getProto()->getExpandedPropertyList(
                                $property->getName() . ':'
                            )
                        );
                } else {
                    $lists[$className][$prefix . $property->getName()]
                        = $property;
                }
            }
        }

        return $lists[$className];
    }

    /**
     * @return LightMetaProperty
     * @throws MissingElementException
     **/
    public function getPropertyByName($name)
    {
        if ($property = $this->safePropertyGet($name))
            return $property;

        throw new MissingElementException(
            get_class($this) . ": unknown property requested by name '{$name}'"
        );
    }

    public function isPropertyExists($name)
    {
        return $this->safePropertyGet($name) !== null;
    }

    /**
     * @return Form
     **/
    public function makeForm($prefix = null)
    {
        $form = Form::create();

        foreach ($this->getPropertyList() as $property) {
            $property->fillForm($form, $prefix);
        }

        return $form;
    }

    /**
     * @return InsertOrUpdateQuery
     **/
    public function fillQuery(
        InsertOrUpdateQuery $query,
        Prototyped $object,
        Prototyped $old = null
    )
    {
        if ($old) {
            if ($object instanceof Identifiable) {
                Assert::isNotNull($object->getId());

                Assert::isTypelessEqual(
                    $object->getId(), $old->getId(),
                    'cannot merge different objects'
                );
            }
        }

        foreach ($this->getPropertyList() as $property) {
            $property->fillQuery($query, $object, $old);
        }

        return $query;
    }

    public function getMapping()
    {
        static $mappings = [];

        $className = get_class($this);

        if (!isset($mappings[$className])) {
            $mapping = [];
            foreach ($this->getPropertyList() as $property) {
                $mapping = $property->fillMapping($mapping);
            }
            $mappings[$className] = $mapping;
        }

        return $mappings[$className];
    }

    public function importPrimitive(
        $path,
        Form $form,
        BasePrimitive $prm,
        /* Prototyped */
        $object,
        $ignoreNull = true
    )
    {
        if (strpos($path, ':') !== false) {
            return $this->forwardPrimitive(
                $path, $form, $prm, $object, $ignoreNull
            );
        } else {
            $property = $this->getPropertyByName($path);
            $getter = $property->getGetter();

            if ($path == 'id' && $prm instanceof PrimitiveIdentifier) {
                $form->importValue($prm->getName(), $object);
                return $object;
            }

            if (
                !$property->isFormless()
                && ($property->getFetchStrategyId() == FetchStrategy::LAZY)
                && !$object->{$getter . 'Id'}()
            ) {
                return $object;
            }

            $value = $object->$getter();

            if (!$ignoreNull || ($value !== null)) {
                $form->importValue($prm->getName(), $value);
            }
        }

        return $object;
    }

    public function exportPrimitive(
        $path,
        BasePrimitive $prm,
        /* Prototyped */
        $object,
        $ignoreNull = true
    )
    {
        if (strpos($path, ':') !== false) {
            return $this->forwardPrimitive(
                $path, null, $prm, $object, $ignoreNull
            );
        } else {
            $property = $this->getPropertyByName($path);
            $setter = $property->getSetter();
            $value = $prm->getValue();

            if (
                !$ignoreNull || ($value !== null)
            ) {
                if ($property->isIdentifier()) {
                    $value = $value->getId();
                }

                $dropper = $property->getDropper();

                if (
                    ($value === null)
                    && method_exists($object, $dropper)
                    && (
                        !$property->getRelationId()
                        || (
                            $property->getRelationId()
                            == MetaRelation::ONE_TO_ONE
                        )
                    )
                ) {
                    $object->$dropper();

                    return $object;
                } elseif (
                    (
                        $property->getRelationId()
                        == MetaRelation::ONE_TO_MANY
                    ) || (
                        $property->getRelationId()
                        == MetaRelation::MANY_TO_MANY
                    )
                ) {
                    if ($value === null)
                        $value = array();

                    $getter = $property->getGetter();
                    $object->$getter()->setList($value);

                    return $object;
                }

                $object->$setter($value);
            }
        }

        return $object;
    }

    private static function fetchEncapsulants(Prototyped $object)
    {
        $proto = $object->proto();

        foreach ($proto->getPropertyList() as $property) {
            if (
                $property->getRelationId() == MetaRelation::ONE_TO_ONE
                && ($property->getFetchStrategyId() != FetchStrategy::LAZY)
            ) {
                $getter = $property->getGetter();
                $setter = $property->getSetter();

                if (($inner = $object->$getter()) instanceof DAOConnected) {
                    if ($proto->depth)
                        $proto->storage[$proto->depth][$setter][] = $inner;
                    else
                        $object->$setter(
                            $inner->dao()->getById(
                                $inner->getId()
                            )
                        );
                } elseif (
                    $proto->depth
                    // emulating 'instanceof DAOConnected'
                    && method_exists($property->getClassName(), 'dao')
                )
                    $proto->storage[$proto->depth][$setter][] = null;
            }
        }

        return $object;
    }

    private static function assemblyObject(
        Prototyped $object, $array, $prefix = null, ProtoDAO $parentDao = null
    )
    {
        if ($object instanceof DAOConnected)
            $dao = $object->dao();
        else
            $dao = $parentDao ?: null;

        $proto = $object->proto();

        foreach ($proto->getPropertyList() as $property) {
            $setter = $property->getSetter();

            if ($property instanceof InnerMetaProperty) {
                $object->$setter(
                    $property->toValue($dao, $array, $prefix)
                );
            } elseif ($property->isBuildable($array, $prefix)) {
                if ($property->getRelationId() == MetaRelation::ONE_TO_ONE) {
                    if (
                        $property->getFetchStrategyId()
                        == FetchStrategy::LAZY
                    ) {
                        $columnName = $prefix . $property->getColumnName();

                        $object->
                        {$setter . 'Id'}($array[$columnName]);

                        continue;
                    }
                }

                $object->$setter($property->toValue($dao, $array, $prefix));
            }
        }

        return $object;
    }

    private function forwardPrimitive(
        $path,
        Form $form = null,
        BasePrimitive $prm,
        /* Prototyped */
        $object,
        $ignoreNull = true
    )
    {
        list($propertyName, $path) = explode(':', $path, 2);

        $property = $this->getPropertyByName($propertyName);

        Assert::isTrue($property instanceof InnerMetaProperty);

        $getter = $property->getGetter();

        if ($form)
            return $property->getProto()->importPrimitive(
                $path, $form, $prm, $object->$getter(), $ignoreNull
            );
        else
            return $property->getProto()->exportPrimitive(
                $path, $prm, $object->$getter(), $ignoreNull
            );
    }

    private function safePropertyGet($name)
    {
        $list = $this->getPropertyList();

        if (isset($list[$name]))
            return $list[$name];

        return null;
    }
}

?>