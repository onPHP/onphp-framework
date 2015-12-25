<?php
/*****************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                               *
 *                                                                           *
 *   This program is free software; you can redistribute it and/or modify    *
 *   it under the terms of the GNU Lesser General Public License as          *
 *   published by the Free Software Foundation; either version 3 of the      *
 *   License, or (at your option) any later version.                         *
 *                                                                           *
 *****************************************************************************/

/**
 * @ingroup Primitives
 **/
class PrimitiveEnumerationByValue extends PrimitiveEnumeration
{
    /**
     * @param $scope
     * @return bool|null
     * @throws WrongStateException
     */
    public function import($scope) : bool
    {
        if (!$this->className) {
            throw new WrongStateException(
                "no class defined for PrimitiveEnumeration '{$this->name}'"
            );
        }

        if (isset($scope[$this->name])) {
            $scopedValue = urldecode($scope[$this->name]);

            $anyId =
                ClassUtils::callStaticMethod($this->className . '::getAnyId');

            $object = new $this->className($anyId);

            $names = $object->getNameList();

            foreach ($names as $key => $value) {
                if ($value == $scopedValue) {
                    try {
                        $this->value = new $this->className($key);
                    } catch (MissingElementException $e) {
                        $this->value = null;
                        return false;
                    }

                    return true;
                }
            }

            return false;
        }

        return null;
    }
}