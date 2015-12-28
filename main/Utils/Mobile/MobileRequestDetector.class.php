<?php
/***************************************************************************
 *   Copyright (C) 2008 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Try to identify mobile device by http headers
 *
 * @ingroup Utils
 **/
final class MobileRequestDetector
{
    static private $headers = [
        'HTTP_X_WAP_PROFILE',
        'HTTP_PROFILE',
        // has additional info
        'HTTP_X_OS_PREFS',
        // msisdn stuff
        'HTTP_MSISDN',
        'HTTP_X_MSISDN',
        'HTTP_X_NOKIA_MSISDN',
        'HTTP_X_WAP_NETWORK_CLIENT_MSISDN',
        'HTTP_X_UP_CALLING_LINE_ID',
        'HTTP_X_NETWORK_INFO',
        // device caps
        'HTTP_X_UP_DEVCAP_ISCOLOR',
        // ms specific headers
        'HTTP_UA_PIXELS',
        'HTTP_UA_COLOR',
        // TODO: specify value range
        //'HTTP_UA_OS',
        //'HTTP_UA_CPU',
        'HTTP_UA_VOICE',
        // misc
        'HTTP_X_NOKIA_BEARER',
        'HTTP_X_NOKIA_GATEWAY_ID',
        'HTTP_X_NOKIA_WIA_ACCEPT_ORIGINAL',
        'HTTP_X_NOKIA_CONNECTION_MODE',
        'HTTP_X_NOKIA_WTLS',
        'HTTP_X_WAP_PROXY_COOKIE',
        'HTTP_X_WAP_TOD_CODED',
        'HTTP_X_WAP_TOD',
        'HTTP_X_UNIQUEWCID',
        'HTTP_WAP_CONNECTION',
        'HTTP_X_WAP_GATEWAY',
        'HTTP_X_WAP_SESSION_ID',
        'HTTP_X_WAP_NETWORK_CLIENT_IP',
        'HTTP_X_WAP_CLIENT_SDU_SIZE',
        'HTTP_ACCEPT_APPLICATION',
        'HTTP_X_ZTGO_BEARERINFO',
        // lg specific ?
        'HTTP_BEARER_INDICATION'
    ];

    /**
     * @deprecated
     *
     * @return MobileRequestDetector
     **/
    public static function create()
    {
        return new self;
    }

    public function isMobile(array $source, $checkAccept = false)
    {
        if ($this->isOperaMini($source)) {
            return true;
        }

        foreach (self::$headers as $header) {
            if (isset($source[$header])) {
                return true;
            }
        }

        if ($this->isIphone($source)) {
            return true;
        }

        if ($checkAccept) {
            return $this->isMobileByHttpAccept($source);
        }

        return false;
    }

    public function isOperaMini(array $source)
    {
        // mandatory opera mini header
        return isset($source['HTTP_X_OPERAMINI_FEATURES']);
    }

    public function isIphone(array $source)
    {
        return (
            isset($source['HTTP_USER_AGENT'])
            &&
            stripos(
                $source['HTTP_USER_AGENT'],
                'iphone'
            ) !== false
        );
    }

    public function isMobileByHttpAccept(array $source)
    {
        return (
            isset($source['HTTP_ACCEPT'])
            &&
            stripos(
                $source['HTTP_ACCEPT'],
                'vnd.wap.wml'
            ) !== false
        );
    }
}

?>