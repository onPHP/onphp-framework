<?php
/***************************************************************************
 *   Copyright (C) 2021 by Sergei V. Deriabin                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Markup\OGP;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Exception\WrongArgumentException;

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
	const ALLOWED_GENDER = ['male', 'female'];

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

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return static
	 * @throws WrongArgumentException
	 */
    public function set(string $name, $value): OpenGraphProfile
    {
		if ($name == 'gender') {
			Assert::isTrue(
				in_array($value, self::ALLOWED_GENDER),
				'Only `male` or `female` are allowed'
			);
	    }

	    return parent::set($name, $value);
    }
}