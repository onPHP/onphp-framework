<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Primitives
 **/
class PrimitiveDate extends ComplexPrimitive
{
    const
        DAY = 'day',
        MONTH = 'month',
        YEAR = 'year';

    /**
     * @param $object
     * @return PrimitiveDate
     */
    public function setValue($object) : PrimitiveDate
    {
        $this->checkType($object);

        $this->value = $object;

        return $this;
    }

    /**
     * @param $object
     * @throws WrongArgumentException
     */
    protected function checkType($object)
    {
        Assert::isTrue(
            ClassUtils::isInstanceOf($object, $this->getObjectName())
        );
    }

    /**
     * default class name
     *
     * @return string
     */
    protected function getObjectName()
    {
        return 'Date';
    }

    /**
     * @param Date $object
     * @return PrimitiveDate
     */
    public function setMin($object) : PrimitiveDate
    {
        $this->checkType($object);

        $this->min = $object;

        return $this;
    }

    /**
     * @param Date $object
     * @return PrimitiveDate
     */
    public function setMax($object) {
        $this->checkType($object);

        $this->max = $object;

        return $this;
    }

    /**
     * @param Date $object
     * @return $this
     */
    public function setDefault($object) {
        $this->checkType($object);

        $this->default = $object;

        return $this;
    }

    /**
     * @param $value
     * @return bool|null
     */
    public function importValue($value)
    {
        /** @var Date $value*/
        if ($value) {
            $this->checkType($value);
        } else {
            return parent::importValue(null);
        }


        $singleScope = [$this->getName() => $value->toString()];
        $marriedRaw =
            [
                self::DAY => $value->getDay(),
                self::MONTH => $value->getMonth(),
                self::YEAR => $value->getYear(),
            ];


        /** @var Timestamp $value*/
        if ($value instanceof Timestamp) {
            $marriedRaw[PrimitiveTimestamp::HOURS] = $value->getHour();
            $marriedRaw[PrimitiveTimestamp::MINUTES] = $value->getMinute();
            $marriedRaw[PrimitiveTimestamp::SECONDS] = $value->getSecond();
        }

        $marriedScope = [$this->getName() => $marriedRaw];

        if ($this->getState()->isTrue()) {
            return $this->importSingle($singleScope);
        } elseif ($this->getState()->isFalse()) {
            return $this->importMarried($marriedScope);
        } else {
            if (!$this->importMarried($marriedScope)) {
                return $this->importSingle($singleScope);
            }

            return $this->imported = true;
        }
    }

    /**
     * @param $scope
     * @return bool|null
     */
    public function importSingle($scope)
    {
        if (
            BasePrimitive::import($scope)
            && (
                is_string($scope[$this->name])
                || is_numeric($scope[$this->name])
            )
        ) {
            try {
                $class = $this->getObjectName();
                $ts = new $class($scope[$this->name]);
            } catch (WrongArgumentException $e) {
                return false;
            }

            if ($this->checkRanges($ts)) {
                $this->value = $ts;
                return true;
            }
        } elseif ($this->isEmpty($scope)) {
            return null;
        }

        return false;
    }

    /**
     * @param Date $date
     * @return bool
     */
    protected function checkRanges(Date $date) : bool
    {
        return
            (!$this->min || ($this->min->toStamp() <= $date->toStamp()))
            && (!$this->max || ($this->max->toStamp() >= $date->toStamp()));
    }

    /**
     * @param $scope
     * @return bool
     */
    public function isEmpty($scope) : bool
    {
        if (
            $this->getState()->isFalse()
            || $this->getState()->isNull()
        ) {
            return empty($scope[$this->name][self::DAY])
            && empty($scope[$this->name][self::MONTH])
            && empty($scope[$this->name][self::YEAR]);
        } else {
            return empty($scope[$this->name]);
        }
    }

    /**
     * @param $scope
     * @return bool
     */
    public function importMarried($scope)
    {
        if (
            BasePrimitive::import($scope)
            && isset(
                $scope[$this->name][self::DAY],
                $scope[$this->name][self::MONTH],
                $scope[$this->name][self::YEAR]
            )
            && is_array($scope[$this->name])
        ) {
            if ($this->isEmpty($scope)) {
                return !$this->isRequired();
            }

            $year = (int) $scope[$this->name][self::YEAR];
            $month = (int) $scope[$this->name][self::MONTH];
            $day = (int) $scope[$this->name][self::DAY];

            if (!checkdate($month, $day, $year)) {
                return false;
            }

            try {
                $date = new Date(
                    $year . '-' . $month . '-' . $day
                );
            } catch (WrongArgumentException $e) {
                // fsck wrong dates
                return false;
            }

            if ($this->checkRanges($date)) {
                $this->value = $date;
                return true;
            }
        }

        return false;
    }

    /* void */
    /**
     * @return array|null
     */
    public function exportValue()
    {
        if ($this->value === null) {
            if ($this->getState()->isTrue()) {
                return null;
            } else {
                return [
                    self::DAY => null,
                    self::MONTH => null,
                    self::YEAR => null,
                ];
            }
        }

        if ($this->getState()->isTrue()) {
            return $this->value->toString();
        } else {
            return [
                self::DAY => $this->value->getDay(),
                self::MONTH => $this->value->getMonth(),
                self::YEAR => $this->value->getYear(),
            ];
        }
    }
}