<?php
/***************************************************************************
 *   Copyright (C) 2011 by Alexander A. Zaytsev                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Flow
 **/
class EmptyGifView implements View
{
    /**
     * @deprecated
     * @return EmptyGifView
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @return EmptyGifView
     **/
    public function render(/* Model */
        $model = null
    ) {
        header('Content-Type: image/gif');
        header('Content-Length: 43');
        header('Accept-Ranges: none');

        // NOTE: this is hardcoded empty gif 1x1 image
        print
            "GIF89\x61\x01\x00\x01\x00\x80\x00\x00\xff\xff\xff\x00"
            . "\x00\x00\x21\xf9\x04\x01\x00\x00\x00\x00\x2c\x00\x00\x00"
            . "\x00\x01\x00\x01\x00\x00\x02\x02\x44\x01\x00\x3b";

        return $this;
    }
}
