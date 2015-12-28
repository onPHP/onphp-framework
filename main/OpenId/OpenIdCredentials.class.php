<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup OpenId
 **/
class OpenIdCredentials
{
    const HEADER_CONT_TYPE = 'application/xrds+xml';
    const HEADER_XRDS_LOCATION = 'x-xrds-location';
    const HEADER_ACCEPT = 'text/html,application/xhtml+xml,application/xml,application/xrds+xml';
    const IDENTIFIER_SELECT = 'http://specs.openid.net/auth/2.0/identifier_select';

    private $claimedId = null;
    private $realId = null;
    private $server = null;
    private $httpClient = null;
    private $isIdentifierSelect = false;

    public function __construct(
        HttpUrl $claimedId,
        HttpClient $httpClient
    ) {
        $this->claimedId = $claimedId->makeComparable();

        if (!$claimedId->isValid()) {
            throw new OpenIdException('invalid claimed id');
        }

        $this->httpClient = $httpClient;

        $response = $httpClient->send(
            (new HttpRequest())
                ->setHeaderVar('Accept', self::HEADER_ACCEPT)
                ->setMethod(HttpMethod::get())
                ->setUrl($claimedId)
        );

        if ($response->getStatus()->getId() != 200) {
            throw new OpenIdException('can\'t fetch document');
        }

        $contentType = $response->getHeader('content-type');
        if (mb_stripos($contentType, self::HEADER_CONT_TYPE) !== false) {
            $this->parseXRDS($response->getBody());
        } elseif ($response->hasHeader(self::HEADER_XRDS_LOCATION)) {
            $this->loadXRDS($response->getHeader(self::HEADER_XRDS_LOCATION));
        } else {
            $this->parseHTML($response->getBody());
        }

        if (!$this->server || !$this->server->isValid()) {
            throw new OpenIdException('bad server');
        } else {
            $this->server->makeComparable();
        }

        if (!$this->realId) {
            $this->realId = $claimedId;
        } elseif (!$this->realId->isValid()) {
            throw new OpenIdException('bad delegate');
        } else {
            $this->realId->makeComparable();
        }
    }

    protected function parseXRDS($content)
    {
        if (preg_match('|<URI>(.*?)</URI>|uis', $content, $match)) {
            $this->server = (new HttpUrl())->parse($match[1]);
        }

        return $this;
    }

    protected function loadXRDS($url)
    {
        $response = $this->httpClient->send(
            (new HttpRequest())
                ->setHeaderVar('Accept', self::HEADER_ACCEPT)
                ->setMethod(HttpMethod::get())
                ->setUrl((new HttpUrl())->parse($url))
        );

        if ($response->getStatus()->getId() != 200) {
            throw new OpenIdException('can\'t fetch document');
        }

        $this->parseXRDS($response->getBody());

        return $this;
    }

    protected function parseHTML($content)
    {
        $tokenizer = (new HtmlTokenizer(new StringInputStream($content)))
            ->lowercaseTags(true)
            ->lowercaseAttributes(true);

        $insideHead = false;
        while ($token = $tokenizer->nextToken()) {
            if (!$insideHead) {
                if ($token instanceof SgmlOpenTag
                    && $token->getId() == 'head'
                ) {
                    $insideHead = true;
                    continue;
                }
            }

            if ($insideHead) {
                if ($token instanceof SgmlEndTag && $token->getId() == 'head') {
                    break;
                }

                if (
                    $token instanceof SgmlOpenTag
                    && $token->getId() == 'link'
                    && $token->hasAttribute('rel')
                    && $token->hasAttribute('href')
                ) {
                    if ($token->getAttribute('rel') == 'openid.server') {
                        $this->server = (new HttpUrl())
                            ->parse($token->getAttribute('href'));
                    }

                    if ($token->getAttribute('rel') == 'openid.delegate') {
                        $this->realId = (new HttpUrl())->parse(
                            $token->getAttribute('href')
                        );
                    }
                }

                if (
                    $token instanceof SgmlOpenTag
                    && $token->getId() == 'meta'
                    && $token->hasAttribute('content')
                    && $token->hasAttribute('http-equiv')
                    && mb_strtolower($token->getAttribute('http-equiv'))
                    == self::HEADER_XRDS_LOCATION
                ) {
                    $this->loadXRDS($token->getAttribute('content'));

                    return $this;
                }
            }
        }

        return $this;
    }

    /**
     * @return OpenIdCredentials
     **/
    public static function create(
        HttpUrl $claimedId,
        HttpClient $httpClient
    ) {
        return new self($claimedId, $httpClient);
    }

    /**
     * @return HttpUrl
     **/
    public function getRealId()
    {
        if ($this->isIdentifierSelect()) {
            return (new HttpUrl())->parse(self::IDENTIFIER_SELECT);
        }

        return $this->realId;
    }

    public function isIdentifierSelect()
    {
        return $this->isIdentifierSelect;
    }

    /**
     * @return HttpUrl
     **/
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @return OpenIdCredentials
     **/
    public function setIdentifierSelect($bool)
    {
        $this->isIdentifierSelect = (bool) $bool;

        return $this;
    }
}

