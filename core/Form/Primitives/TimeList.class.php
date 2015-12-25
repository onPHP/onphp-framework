<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov, Igor V. Gulyaev    *
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
class TimeList extends BasePrimitive
{
    /** @var array  */
    protected $value = [];

    /**
     * @return TimeList
     **/
    public function clean() : TimeList
    {
        parent::clean();

        $this->value = [];

        return $this;
    }

    /**
     * @param $scope
     * @return bool|null
     */
    public function import($scope)
    {
        if (
            empty($scope[$this->name])
            || !is_array($scope[$this->name])
        ) {
            return null;
        }

        $this->raw = $scope[$this->name];
        $this->imported = true;

        $array = $scope[$this->name];
        $list = [];

        foreach ($array as $string) {
            $timeList = self::stringToTimeList($string);

            if ($timeList) {
                $list[] = $timeList;
            }
        }

        $this->value = $list;

        return ($this->value !== []);
    }

    /**
     * @param $string
     * @return array
     */
    public static function stringToTimeList($string)
    {
        $list = [];

        //$times = split("([,; \n]+)", $string);
        $times = explode([',',';'], $string);

        for ($i = 0, $size = count($times); $i < $size; ++$i) {
            $time = mb_ereg_replace('[^0-9:]', ':', $times[$i]);

            try {
                $list[] = new Time($time);
            } catch (WrongArgumentException $e) {/* ignore */
            }
        }

        return $list;
    }

    /**
     * @return array
     */
    public function getValueOrDefault() : array
    {
        if (is_array($this->value) && $this->value[0]) {
            return $this->value;
        }

        return [$this->default];
    }

    /**
     * @return array
     */
    public function getActualValue() : array
    {
        return [$this->getValueOrDefault()];
    }

    public function exportValue()
    {
        throw new UnimplementedFeatureException();
    }
}