<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Anton E. Lebedevich                        *
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
final class PrimitiveMultiList extends PrimitiveList
{
    private $selected = [];

    /**
     * @return array
     */
    public function getChoiceValue() : array
    {
        return $this->selected;
    }

    /**
     * @return array
     */
    public function getActualChoiceValue() : array
    {
        if ($this->value !== null) {
            return $this->selected;
        } elseif ($this->default) {
            $out = [];

            foreach ($this->default as $index) {
                $out[] = $this->list[$index];
            }

            return $out;
        }

        return [];
    }

    /**
     * @param $default
     * @return BasePrimitive
     * @throws WrongArgumentException
     */
    public function setDefault($default)
    {
        Assert::isArray($default);

        foreach ($default as $index) {
            Assert::isTrue(array_key_exists($index, $this->list));
        }

        return parent::setDefault($default);
    }

    /**
     * @param $scope
     * @return bool|null
     * @throws WrongStateException
     */
    public function import($scope)
    {
        if (!BasePrimitive::import($scope)) {
            return null;
        }

        if (!$this->list) {
            throw new WrongStateException(
                'list to check is not set; '
                . 'use PrimitiveArray in case it is intentional'
            );
        }

        if (is_array($scope[$this->name])) {
            $values = [];

            foreach ($scope[$this->name] as $value) {
                if (isset($this->list[$value])) {
                    $values[] = $value;
                    $this->selected[$value] = $this->list[$value];
                }
            }

            if (count($values)) {
                $this->value = $values;

                return true;
            }
        } elseif (!empty($scope[$this->name])) {
            $this->value = [$scope[$this->name]];

            return true;
        }

        return false;
    }

    /**
     * @return PrimitiveMultiList
     */
    public function clean() : PrimitiveMultiList
    {
        $this->selected = [];

        return parent::clean();
    }

    /**
     * @throws UnimplementedFeatureException
     */
    public function exportValue()
    {
        throw new UnimplementedFeatureException();
    }
}

?>