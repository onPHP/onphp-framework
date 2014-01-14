<?php
/***************************************************************************
 *   Copyright (C) 2013 by Alexey Solomonov                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Данный класс создан для удобства работы с переводимыми полями
 *
 * @ingroup DAOs
 **/
abstract class TranslatableDAO extends GenericDAO {

    public function isTranslatedField($name) {
        /** @var $proto AbstractProtoClass */
        $proto = $this->getProtoClass();
        return $proto->isPropertyExists($name)
            && $proto->getPropertyByName($name)->isTranslated();
    }

    public function getLanguageCode() {
        return call_user_func(array($this->getObjectName(), 'getLanguageCode'));
    }
} 