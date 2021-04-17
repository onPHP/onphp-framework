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
 * Class OpenGraphArticle
 * @see https://ogp.me/#type_article
 *
 * published_time - string datetime ISO 8601 - When the article was first published.
 * modified_time - string datetime ISO 8601 - When the article was last changed.
 * expiration_time  - string datetime ISO 8601 - When the article is out of date after.
 * author - string[] - Writers of the article.
 * section - string - A high-level section name. E.g. Technology
 * tag - string[] - Tag words associated with this article.
 *
 * @ingroup Markup
 * @ingroup OGP
 */
class OpenGraphArticle extends OpenGraphObject
{
    /**
     * OpenGraphArticle constructor.
     */
    public function __construct()
    {
        $this->type = new OpenGraphType(OpenGraphType::ARTICLE_ID);
        $this->namespace = 'article';
        $this->items = [
            'published_time' => null,
            'modified_time' => null,
            'expiration_time' => null,
            'author' => [],
            'section' => null,
            'tag' => [],
        ];
    }
}