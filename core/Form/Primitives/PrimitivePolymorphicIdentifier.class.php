<?php
/***************************************************************************
 *   Copyright (C) 2008 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Hint: use raw values like 'City.42' or 'Country.42' where City and
 * Country are childrens of base class GeoLocation, for example.
 **/
final class PrimitivePolymorphicIdentifier extends PrimitiveIdentifier
{
    const
        WRONG_CID_FORMAT = 201,
        WRONG_CLASS = 202;

    const
        DELIMITER = '.';

    /** @var null */
    private $baseClassName = null;

    /**
     * @throws WrongStateException
     **/
    public function of($class)
    {
        throw new WrongStateException(
            'of() must not be called directly, use ofBase()'
        );
    }

    /**
     * @param $className
     * @return PrimitivePolymorphicIdentifier
     * @throws WrongArgumentException
     */
    public function ofBase($className)
    {
        Assert::classExists($className);

        Assert::isInstance(
            $className,
            'DAOConnected',
            "class '{$className}' must implement DAOConnected interface"
        );

        $this->baseClassName = $className;

        return $this;
    }

    /**
     * @return null
     */
    public function getBaseClassName()
    {
        return $this->baseClassName;
    }

    /**
     * @param $value
     * @return IdentifiablePrimitive
     * @throws WrongArgumentException
     */
    public function setValue($value)
    {
        Assert::isInstance($value, $this->baseClassName);

        parent::of(get_class($value));

        return parent::setValue($value);
    }

    /**
     * @return null|string
     */
    public function exportValue()
    {
        if ($this->value === null) {
            return null;
        }

        return self::export($this->value);
    }

    /**
     * @param $value
     * @return null|string
     * @throws WrongArgumentException
     */
    public static function export($value)
    {
        if ($value === null) {
            return null;
        }

        Assert::isInstance($value, 'Identifiable');

        return get_class($value) . self::DELIMITER . $value->getId();
    }

    /**
     * @param $value
     * @return bool|mixed|null
     */
    public function importValue($value)
    {
        return $this->import(
            [
                $this->getName() => self::export($value)
            ]
        );
    }

    /**
     * @param $scope
     * @return bool|mixed|null
     * @throws WrongArgumentException
     * @throws WrongStateException
     */
    public function import($scope)
    {
        $savedRaw = null;

        if (isset($scope[$this->name]) && $scope[$this->name]) {
            $savedRaw = $scope[$this->name];

            $this->customError = null;

            try {

                list($class, $id) = explode(self::DELIMITER, $savedRaw, 2);

            } catch (BaseException $e) {

                $this->customError = self::WRONG_CID_FORMAT;

            }

            if (
                !$this->customError
                && !ClassUtils::isInstanceOf($class, $this->baseClassName)
            ) {

                $this->customError = self::WRONG_CLASS;

            }


            if (!$this->customError) {
                parent::of($class);

                $scope[$this->name] = $id;
            }

        } else {
            // we need some class in any case
            parent::of($this->baseClassName);
        }

        if (!$this->customError) {
            $result = parent::import($scope);
        } else {
            $this->value = null;
            $result = false;
        }

        if ($savedRaw) {
            $this->raw = $savedRaw;
        }

        return $result;
    }
}
