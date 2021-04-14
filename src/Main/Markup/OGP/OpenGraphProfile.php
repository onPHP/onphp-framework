<?php
/***************************************************************************
 *   Copyright (C) 2007 by Sergei V. Deriabin                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Markup\OGP;

/**
 * Class OpenGraphProfile
 * @see https://ogp.me/#type_profile
 *
 * first_name - string - A name normally given to an individual by a parent or self-chosen.
 * last_name - string - A name inherited from a family or marriage and by which the individual is commonly known.
 * username - string - A short unique string to identify them.
 * gender - string (male, female) - Their gender.
 *
 * @ingroup Markup
 * @ingroup OGP
 */
class OpenGraphProfile extends OpenGraphObject
{
    /**
     * OpenGraphProfile constructor.
     */
    public function __construct()
    {
        $this->type = new OpenGraphType(OpenGraphType::PROFILE_ID);
        $this->namespace = 'profile';
        $this->items = [
            'first_name' => null,
            'last_name' => null,
            'username' => null,
            'gender' => null,
        ];
    }
}