<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Containers
 **/
class OneToManyLinkedLazy extends OneToManyLinkedWorker
{
    /**
     * @return SelectQuery
     **/
    public function makeFetchQuery()
    {
        $query =
            $this
                ->makeSelectQuery()
                ->dropFields()
                ->get($this->container->getChildIdField());

        return $this->targetize($query);
    }

    /**
     * @throws WrongArgumentException
     * @return OneToManyLinkedLazy
     **/
    public function sync($insert, $update = [], $delete)
    {
        Assert::isTrue($update === []);

        $db = DBPool::getByDao($this->container->getDao());

        $uc = $this->container;
        $dao = $uc->getDao();

        if ($insert) {
            $db->queryNull($this->makeMassUpdateQuery($insert));
        }

        if ($delete) {
            // unlink or drop
            $uc->isUnlinkable()
                ?
                $db->queryNull($this->makeMassUpdateQuery($delete))
                :
                $db->queryNull(
                    (new OSQL())
                        ->delete()
                        ->from($dao->getTable())
                        ->where(
                            Expression::in(
                                $uc->getChildIdField(),
                                $delete
                            )
                        )
                );

            $dao->uncacheByIds($delete);
        }

        return $this;
    }

    /**
     * @return UpdateQuery
     **/
    private function makeMassUpdateQuery($ids)
    {
        $uc = $this->container;

        return
            (new OSQL())
                ->update($uc->getDao()->getTable())
                ->set($uc->getParentIdField(), null)
                ->where(
                    Expression::in(
                        $uc->getChildIdField(),
                        $ids
                    )
                );
    }
}

