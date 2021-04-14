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
 * @see https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/app-card
 *
 * card - string - Must be set to a value of “app”
 * site - string - The Twitter @username the card should be attributed to.
 * description - string - You can use this as a more concise description than what
 *      you may have on the app store. This field has a maximum of 200 characters
 * app:name:iphone - string - Name of your iPhone app
 * app:id:iphone - string - String value, and should be the numeric representation of
 *      your app ID in the App Store (.i.e. “307234931”).
 *      Your app ID in the iTunes App Store (Note: NOT your bundle ID)
 * app:url:iphone - string - Your app’s custom URL scheme (you must include ”://” after your scheme name)
 * app:name:ipad - string - Name of your iPad optimized app
 * app:id:ipad - string - String value, should be the numeric
 *      representation of your app ID in the App Store (.i.e. “307234931”)
 *      Your app ID in the iTunes App Store
 * app:url:ipad - string - Your app’s custom URL scheme
 * app:name:googleplay - string - Name of your Android app
 * app:id:googleplay - string - String value, and should be the numeric
 *      representation of your app ID in Google Play (.i.e. “com.android.app”)
 * app:url:ipad - string - Your app’s custom URL scheme
 * app:url:googleplay - string - Your app’s custom URL scheme
 * app:country - string - If your application is not available in the US App Store, you must set
 *      this value to the two-letter country code for the App Store that contains your application.
 *
 * @ingroup Markup
 * @ingroup OGP
 */
class OpenGraphTwitterAppCard extends OpenGraphTwitterCard
{
    protected string $type = 'app';

    public function __construct()
    {
        $this->items += [
            'app:name:iphone' => null,
            'app:id:iphone' => null,
            'app:url:iphone' => null,
            'app:name:ipad' => null,
            'app:id:ipad' => null,
            'app:url:ipad' => null,
            'app:name:googleplay' => null,
            'app:id:googleplay' => null,
            'app:url:googleplay' => null,
            'app:country' => null,
        ];
    }
}