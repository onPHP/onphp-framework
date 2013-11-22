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
 * Хранит переводы свойства объекта
 * Класс необходим для разделения обычного Hstore и Hstore используемого в переводах
 * Например, при создании формы из объекта необходимо исключить попадание TranslatedStore свойства в форму
 * @ingroup Helpers
 **/
class TranslatedStore extends Hstore {

    /**
     * Create TranslatedStore by raw string.
     *
     * @return TranslatedStore
     **/
    public static function create($string)
    {
        $self = new self();

        return $self->toValue($string);
    }
}