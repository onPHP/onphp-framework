<?php
/***************************************************************************
 *   Copyright (C) 2007 by Dmitry E. Demidov                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Utils
 **/
final class DaoMoveHelper extends StaticFactory
{
    private static $nullValue = 0;
    private static $property = 'position';

    /* void */
    public static function setNullValue($nullValue)
    {
        self::$nullValue = $nullValue;
    }

    /* void */
    public static function setProperty($property)
    {
        self::$property = $property;
    }

    /* void */
    public static function up(
        DAOConnected $object,
        LogicalObject $exp = null
    ) {
        $getMethod = 'get' . ucfirst(self::$property);

        Assert::isTrue(
            method_exists($object, $getMethod)
        );

        $criteria =
            (new Criteria($object->dao()))
                ->addOrder(
                    (new OrderBy(self::$property))->desc()
                )
                ->setLimit(1);

        if ($exp) {
            $criteria->add($exp);
        }

        $oldPosition = $object->$getMethod();

        $criteria->add(
            Expression::lt(
                self::$property,
                $oldPosition
            )
        );

        if ($upperObject = $criteria->get()) {
            DaoUtils::setNullValue(self::$nullValue);
            DaoUtils::swap($upperObject, $object, self::$property);
        }
    }

    /* void */
    public static function down(
        DAOConnected $object,
        LogicalObject $exp = null
    ) {
        $getMethod = 'get' . ucfirst(self::$property);

        Assert::isTrue(
            method_exists($object, $getMethod)
        );

        $oldPosition = $object->$getMethod();

        $criteria =
            (new Criteria($object->dao()))
                ->add(Expression::gt(self::$property, $oldPosition))
                ->addOrder((new OrderBy(self::$property))->asc())
                ->setLimit(1);

        if ($exp) {
            $criteria->add($exp);
        }

        if ($lowerObject = $criteria->get()) {
            DaoUtils::setNullValue(self::$nullValue);
            DaoUtils::swap($lowerObject, $object, self::$property);
        }
    }
}

