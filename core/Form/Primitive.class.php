<?php
/****************************************************************************
 *   Copyright (C) 2004-2009 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

/**
 * Factory for various Primitives.
 *
 * @ingroup Form
 **/
class Primitive extends StaticFactory
{
    /**
     * @param $primitive
     * @param $name
     * @return BasePrimitive
     * @throws WrongArgumentException
     */
    public static function spawn($primitive, $name)
    {
        Assert::classExists($primitive);

        return new $primitive($name);
    }

    /**
     * @param $name
     * @param BasePrimitive $prm
     * @return PrimitiveAlias
     */
    public static function alias($name, BasePrimitive $prm)
    {
        return new PrimitiveAlias($name, $prm);
    }

    /**
     * @param $name
     * @return PrimitiveAnyType
     */
    public static function anyType($name)
    {
        return new PrimitiveAnyType($name);
    }

    /**
     * @param $name
     * @return PrimitiveInteger
     */
    public static function integer($name)
    {
        return new PrimitiveInteger($name);
    }

    /**
     * @param $name
     * @return PrimitiveFloat
     */
    public static function float($name)
    {
        return new PrimitiveFloat($name);
    }

    /**
     * @param $name
     * @return PrimitiveIdentifier
     */
    public static function identifier($name)
    {
        return new PrimitiveIdentifier($name);
    }

    /**
     * @param $name
     * @return PrimitiveIntegerIdentifier
     */
    public static function integerIdentifier($name)
    {
        return new PrimitiveIntegerIdentifier($name);
    }

    /**
     * @param $name
     * @return PrimitiveScalarIdentifier
     */
    public static function scalarIdentifier($name)
    {
        return new PrimitiveScalarIdentifier($name);
    }

    /**
     * @param $name
     * @return PrimitivePolymorphicIdentifier
     */
    public static function polymorphicIdentifier($name)
    {
        return new PrimitivePolymorphicIdentifier($name);
    }

    /**
     * @param $name
     * @return PrimitiveIdentifierList
     */
    public static function identifierlist($name)
    {
        return new PrimitiveIdentifierList($name);
    }

    /**
     * @param $name
     * @return PrimitiveClass
     */
    public static function clazz($name)
    {
        return new PrimitiveClass($name);
    }

    /**
     * @param $name
     * @return PrimitiveEnumeration
     */
    public static function enumeration($name)
    {
        return new PrimitiveEnumeration($name);
    }

    /**
     * @param $name
     * @return PrimitiveEnumerationByValue
     */
    public static function enumerationByValue($name)
    {
        return new PrimitiveEnumerationByValue($name);
    }

    /**
     * @param $name
     * @return PrimitiveEnumerationList
     */
    public static function enumerationList($name)
    {
        return new PrimitiveEnumerationList($name);
    }

    /**
     * @param $name
     * @return PrimitiveDate
     */
    public static function date($name)
    {
        return new PrimitiveDate($name);
    }

    /**
     * @param $name
     * @return PrimitiveTimestamp
     */
    public static function timestamp($name)
    {
        return new PrimitiveTimestamp($name);
    }

    /**
     * @param $name
     * @return PrimitiveTimestampTZ
     */
    public static function timestampTZ($name)
    {
        return new PrimitiveTimestampTZ($name);
    }

    /**
     * @param $name
     * @return PrimitiveTime
     */
    public static function time($name)
    {
        return new PrimitiveTime($name);
    }

    /**
     * @param $name
     * @return PrimitiveString
     */
    public static function string($name)
    {
        return new PrimitiveString($name);
    }

    /**
     * @param $name
     * @return PrimitiveBinary
     */
    public static function binary($name)
    {
        return new PrimitiveBinary($name);
    }

    /**
     * @param $name
     * @return PrimitiveRange
     */
    public static function range($name)
    {
        return new PrimitiveRange($name);
    }

    /**
     * @param $name
     * @return PrimitiveDateRange
     */
    public static function dateRange($name)
    {
        return new PrimitiveDateRange($name);
    }

    /**
     * @param $name
     * @return PrimitiveTimestampRange
     */
    public static function timestampRange($name)
    {
        return new PrimitiveTimestampRange($name);
    }

    /**
     * @param $name
     * @return PrimitiveList
     */
    public static function choice($name)
    {
        return new PrimitiveList($name);
    }

    /**
     * @param $name
     * @return PrimitiveArray
     */
    public static function set($name)
    {
        return new PrimitiveArray($name);
    }

    /**
     * @param $name
     * @return PrimitiveHstore
     */
    public static function hstore($name)
    {
        return new PrimitiveHstore($name);
    }

    /**
     * @param $name
     * @return PrimitiveMultiList
     */
    public static function multiChoice($name)
    {
        return new PrimitiveMultiList($name);
    }

    /**
     * @param $name
     * @return PrimitivePlainList
     */
    public static function plainChoice($name)
    {
        return new PrimitivePlainList($name);
    }

    /**
     * @param $name
     * @return PrimitiveBoolean
     */
    public static function boolean($name)
    {
        return new PrimitiveBoolean($name);
    }

    /**
     * @param $name
     * @return PrimitiveTernary
     */
    public static function ternary($name)
    {
        return new PrimitiveTernary($name);
    }

    /**
     * @param $name
     * @return PrimitiveFile
     */
    public static function file($name)
    {
        return new PrimitiveFile($name);
    }

    /**
     * @param $name
     * @return PrimitiveImage
     */
    public static function image($name)
    {
        return new PrimitiveImage($name);
    }

    /**
     * @param $name
     * @return ExplodedPrimitive
     */
    public static function exploded($name)
    {
        return new ExplodedPrimitive($name);
    }

    /**
     * @param $name
     * @return PrimitiveInet
     */
    public static function inet($name)
    {
        return new PrimitiveInet($name);
    }

    /**
     * @param $name
     * @return PrimitiveForm
     */
    public static function form($name)
    {
        return new PrimitiveForm($name);
    }

    /**
     * @param $name
     * @return PrimitiveFormsList
     */
    public static function formsList($name)
    {
        return new PrimitiveFormsList($name);
    }

    /**
     * @param $name
     * @return PrimitiveNoValue
     */
    public static function noValue($name)
    {
        return new PrimitiveNoValue($name);
    }

    /**
     * @param $name
     * @return PrimitiveHttpUrl
     */
    public static function httpUrl($name)
    {
        return new PrimitiveHttpUrl($name);
    }

    /**
     * @param $class
     * @param null $name
     * @return BasePrimitive
     * @throws WrongArgumentException
     */
    public static function prototypedIdentifier($class, $name = null)
    {
        Assert::isInstance($class, 'DAOConnected');

        $dao = is_string($class)
            ? call_user_func([$class, 'dao'])
            : $class->dao();

        return self::prototyped($class, $dao->getIdName(), $name);
    }

    /**
     * @param $class
     * @param $propertyName
     * @param null $name
     * @return mixed
     * @throws WrongArgumentException
     */
    public static function prototyped($class, $propertyName, $name = null)
    {
        Assert::isInstance($class, 'Prototyped');

        $proto = is_string($class)
            ? call_user_func([$class, 'proto'])
            : $class->proto();

        if (!$name) {
            $name = $propertyName;
        }

        return $proto
            ->getPropertyByName($propertyName)
            ->makePrimitive($name);
    }

    /**
     * @param $name
     * @return PrimitiveIpAddress
     */
    public static function ipAddress($name)
    {
        return new PrimitiveIpAddress($name);
    }

    /**
     * @param $name
     * @return PrimitiveIpRange
     */
    public static function ipRange($name)
    {
        return new PrimitiveIpRange($name);
    }

    /**
     * @param $name
     * @return PrimitiveEnum
     */
    public static function enum($name)
    {
        return new PrimitiveEnum($name);
    }

    /**
     * @param $name
     * @return PrimitiveEnumByValue
     */
    public static function enumByValue($name)
    {
        return new PrimitiveEnumByValue($name);
    }

    /**
     * @param $name
     * @return PrimitiveEnumList
     */
    public static function enumList($name)
    {
        return new PrimitiveEnumList($name);
    }
}
