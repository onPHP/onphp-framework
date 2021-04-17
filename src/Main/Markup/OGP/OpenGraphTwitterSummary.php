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
 * Summary Card
 * @see https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/summary
 *
 * card - string - Must be set to a value of “summary”
 * title - string - A concise title for the related content. Platform specific behaviors:
 *      - iOS, Android: Truncated to two lines in timeline and expanded Tweet
 *      - Web: Truncated to one line in timeline and expanded Tweet
 * site - string - The Twitter @username the card should be attributed to.
 * site:id - string - Same as twitter:site, but the user’s Twitter ID.
 *      Either twitter:site or twitter:site:id is required.
 * creator:id - string - Twitter user ID of content creator
 * description - string - A description that concisely summarizes the content as appropriate
 *      for presentation within a Tweet. You should not re-use the title as the description
 *      or use this field to describe the general services provided by the website.
 *      Platform specific behaviors:
 *      - iOS, Android: Not displayed
 *      - Web: Truncated to three lines in timeline and expanded Tweet
 * image - string - A URL to a unique image representing the content of the page.
 *      You should not use a generic image such as your website logo, author photo, or other
 *      image that spans multiple pages. Images for this Card support an aspect ratio of 1:1
 *      with minimum dimensions of 144x144 or maximum of 4096x4096 pixels. Images must be less
 *      than 5MB in size. The image will be cropped to a square on all platforms. JPG, PNG,
 *      WEBP and GIF formats are supported. Only the first frame of an animated GIF will be
 *      used. SVG is not supported.
 * image:alt - string - A text description of the image conveying the essential
 *      nature of an image to users who are visually impaired. Maximum 420 characters.
 *
 * @ingroup Markup
 * @ingroup OGP
 */
class OpenGraphTwitterSummary extends OpenGraphTwitterCard
{
	/**
	 * @var string
	 */
    protected string $type = 'summary';

    /**
     * OpenGraphTwitterSummary constructor.
     */
    public function __construct()
    {
        $this->items += [
            'site:id' => null,
            'creator:id' => null,
            'title' => null,
            'image' => null,
            'image:alt' => null,
        ];
    }
}