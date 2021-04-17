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

/**
 * Class OpenGraphBook
 * @see https://ogp.me/#type_book
 *
 * author - string[] - Who wrote this book.
 * isbn - string - The ISBN
 * release_date - string datetime ISO 8601 - The date the book was released.
 * tag - string[] - Tag words associated with this book.
 *
 * @ingroup Markup
 * @ingroup OGP
 */
class OpenGraphBook extends OpenGraphObject
{
    /**
     * OpenGraphBook constructor.
     */
    public function __construct()
    {
        $this->type = new OpenGraphType(OpenGraphType::BOOK_ID);
        $this->namespace = 'book';
        $this->items = [
            'author' => [],
            'isbn' => null,
            'release_date' => null,
            'tag' => [],
        ];
    }
}