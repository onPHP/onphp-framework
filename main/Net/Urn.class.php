<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * URN is an absolute URI without authority part.
 *
 * @ingroup Net
 **/
class Urn extends GenericUri
{
    protected static $knownSubSchemes = [
        'urn' => 'Urn',
        'mailto' => 'Urn',
        'news' => 'Urn',
        'isbn' => 'Urn',
        'tel' => 'Urn',
        'fax' => 'Urn',
    ];
    protected $schemeSpecificPart = null;

    /**
     * @return Urn
     **/
    public static function create()
    {
        return new self;
    }

    public static function getKnownSubSchemes()
    {
        return static::$knownSubSchemes;
    }

    public function isValid()
    {
        if (
            $this->scheme === null
            || $this->getAuthority() !== null
        ) {
            return false;
        }

        return parent::isValid();
    }
}

