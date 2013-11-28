<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Alexey Solomonov                           *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Интерфейса класса возвращающего локаль поумолчанию и текущую локаль проекта
 * Интерфейс необходим при сохраниее и получении TranslatedString свойств
 *
 * @ingroup Base
 * @ingroup Module
 * @see TranslatedString
 **/
interface Translatable {

    public static function getDefaultLanguageCode();
    public static function getLanguageCode();
    public function useTranslatedStore();
    public function setUseTranslatedStore($useTranslatedStore);
}