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
class OneToManyLinkedFull extends OneToManyLinkedWorker
{
    /**
     * @return SelectQuery
     **/
    public function makeFetchQuery()
    {
        return $this->targetize($this->makeSelectQuery());
    }

    /**
     * @return OneToManyLinkedFull
     **/
    public function sync($insert, $update = [], $delete)
    {
        $uc = $this->container;
        $dao = $uc->getDao();

        if ($delete) {
            DBPool::getByDao($dao)->queryNull(
                OSQL::delete()->from($dao->getTable())->
                where(
                    Expression::eq(
                        new DBField($uc->getParentIdField()),
                        $uc->getParentObject()->getId()
                    )
                )->
                andWhere(
                    Expression::in(
                        $uc->getChildIdField(),
                        ArrayUtils::getIdsArray($delete)
                    )
                )
            );

            $dao->uncacheByIds(ArrayUtils::getIdsArray($delete));
        }

        if ($insert) {
            for ($i = 0, $size = count($insert); $i < $size; ++$i) {
                $dao->add($insert[$i]);
            }
        }

        if ($update) {
            for ($i = 0, $size = count($update); $i < $size; ++$i) {
                $dao->save($update[$i]);
            }
        }

        return $this;
    }
}

