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
 * Class OpenGraphWebsite
 * @see https://ogp.me/#type_website
 *
 * No additional properties other than the basic ones.
 * Any non-marked up webpage should be treated as og:type website.
 *
 * @ingroup Markup
 * @ingroup OGP
 */
class OpenGraphWebsite extends OpenGraphObject
{
    /**
     * OpenGraphWebsite constructor.
     */
    public function __construct()
    {
        $this->type = new OpenGraphType(OpenGraphType::WEBSITE_ID);
    }
}