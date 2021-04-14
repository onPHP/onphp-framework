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
 * App Card
 * @see https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/player-card
 *
 * card - string - Must be set to a value of “player”
 * title - string - The title of your content as it should appear in the card
 * site - string - The Twitter @username the card should be attributed to.
 * site:id - string - Same as twitter:site, but the user’s Twitter ID.
 *      Either twitter:site or twitter:site:id is required.
 * description - string - You can use this as a more concise description than what
 *      you may have on the app store. This field has a maximum of 200 characters
 * player - string - HTTPS URL to iFrame player. This must be a HTTPS URL which
 *      does not generate active mixed content warnings in a web browser. The audio
 *      or video player must not require plugins such as Adobe Flash.
 * player:width - string - Width of iFrame specified in twitter:player in pixels
 * player:height - string - Height of iFrame specified in twitter:player in pixels
 * player:stream - string - URL to raw video or audio stream
 * image - string - Image to be displayed in place of the player on platforms that
 *      don’t support iFrames or inline players. You should make this image the same
 *      dimensions as your player. Images with fewer than 68,600 pixels
 *      (a 262x262 square image, or a 350x196 16:9 image) will cause the player card
 *      not to render. Images must be less than 5MB in size. JPG, PNG, WEBP and GIF
 *      formats are supported. Only the first frame of an animated GIF will be used.
 *      SVG is not supported.
 * image:alt - string - A text description of the image conveying the essential
 *      nature of an image to users who are visually impaired. Maximum 420 characters.
 *
 * @ingroup Markup
 * @ingroup OGP
 */
class OpenGraphTwitterPlayerCard extends OpenGraphTwitterCard
{
	/**
	 * @var string
	 */
    protected string $type = 'player';

	/**
	 * OpenGraphTwitterPlayerCard constructor.
	 */
    public function __construct()
    {
        $this->items += [
            'site:id' => null,
            'title' => null,
            'player' => null,
            'player:width' => null,
            'player:height' => null,
            'player:stream' => null,
            'image' => null,
            'image:alt' => null,
        ];
    }
}