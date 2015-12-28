<?php

/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
class FormToArgumentsConverter extends StaticFactory
{
    public static function getShort(Form $form)
    {
        $short = null;

        foreach ($form->getPrimitiveList() as $primitive) {
            if (strlen($primitive->getName()) == 1) {
                $short .=
                    $primitive->getName()
                    . self::getValueType($primitive);
            }
        }

        return $short;
    }

    private static function getValueType(BasePrimitive $primitive)
    {
        if ($primitive instanceof PrimitiveNoValue) {
            return null;
        }

        if ($primitive->isRequired()) {
            return ':';
        } else {
            return '::';
        }
    }

    public static function getLong(Form $form)
    {
        $long = [];

        foreach ($form->getPrimitiveList() as $primitive) {
            if (strlen($primitive->getName()) > 1) {
                $long[] =
                    $primitive->getName()
                    . self::getValueType($primitive);
            }
        }

        return $long;
    }
}

