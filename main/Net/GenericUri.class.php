<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Net
 * @see http://tools.ietf.org/html/rfc3986
 * @todo comparsion
 **/
class GenericUri implements Stringable
{
    const CHARS_UNRESERVED = 'a-z0-9-._~';
    const CHARS_SUBDELIMS = '!$&\'()*+,;=';
    const PATTERN_PCTENCODED = '%[0-9a-f][0-9a-f]';

    protected $scheme = null;

    protected $userInfo = null;
    protected $host = null;
    protected $port = null;

    protected $path = null;
    protected $query = null;
    protected $fragment = null;


    /**
     * @param $uri
     * @param bool|false $guessClass
     * @return static
     * @throws WrongArgumentException
     */
    public function parse($uri, $guessClass = false)
    {
        static $schemePattern = '([^:/?#]+):';
        static $authorityPattern = '(//([^/?#]*))';
        static $restPattern = '([^?#]*)(\?([^#]*))?(#(.*))?';
        $matches = [];

        if (
            $guessClass
            && ($knownSubSchemes = static::getKnownSubSchemes())
            && preg_match("~^{$schemePattern}~", $uri, $matches)
            && isset($knownSubSchemes[strtolower($matches[1])])
        ) {
            $result = new $knownSubSchemes[strtolower($matches[1])];
        } else {
            $result = new static;
        }

        if ($result instanceof Url) {
            $pattern = "({$schemePattern}{$authorityPattern})?";
        } elseif ($result instanceof Urn) {
            $pattern = "({$schemePattern})?";
        } else {
            $pattern = "({$schemePattern})?{$authorityPattern}?";
        }

        $pattern = "~^{$pattern}{$restPattern}$~";

        if (!preg_match($pattern, $uri, $matches)) {
            throw new WrongArgumentException('not well-formed URI');
        }

        array_shift($matches);

        if ($matches[0]) {
            $result->setScheme($matches[1]);
        }

        array_shift($matches);
        array_shift($matches);

        if (!($result instanceof Urn)) {
            if ($matches[0]) {
                $result->setAuthority($matches[1]);
            }

            array_shift($matches);
            array_shift($matches);
        }

        $result->setPath($matches[0]);

        if (!empty($matches[1])) {
            $result->setQuery($matches[2]);
        }

        if (!empty($matches[3])) {
            $result->setFragment($matches[4]);
        }

        return $result;
    }

    public static function getKnownSubSchemes()
    {
        return array_merge(
            Urn::getKnownSubSchemes(),
            Url::getKnownSubSchemes()
        );
    }

    /**
     * @return GenericUri
     **/
    public function setAuthority($authority)
    {
        $authorityPattern = '~^(([^@]*)@)?((\[.+\])|([^:]*))(:(.*))?$~';
        $authorityMatches = [];

        if (
        !preg_match(
            $authorityPattern, $authority, $authorityMatches
        )
        ) {
            throw new WrongArgumentException(
                'not well-formed authority part'
            );
        }

        if ($authorityMatches[1]) {
            $this->setUserInfo($authorityMatches[2]);
        }

        $this->setHost($authorityMatches[3]);

        if (!empty($authorityMatches[6])) {
            $this->setPort($authorityMatches[7]);
        }

        return $this;
    }

    /**
     * @see http://tools.ietf.org/html/rfc3986#section-5.2.2
     * @return GenericUri
     **/
    final public function transform(GenericUri $reference, $strict = true)
    {
        if ($this->getScheme() === null) {
            throw new WrongStateException(
                'URI without scheme cannot be a base URI'
            );
        }

        if (
            $reference->getScheme() !== ($strict ? null : $this->getScheme())
        ) {
            $class = get_class($reference);
            $result = new $class;

            $result
                ->setScheme($reference->getScheme())
                ->setUserInfo($reference->getUserInfo())
                ->setHost($reference->getHost())
                ->setPort($reference->getPort())
                ->setPath(self::removeDotSegments($reference->getPath()))
                ->setQuery($reference->getQuery());
        } else {
            $result = new $this;

            $result->setScheme($this->getScheme());

            if ($reference->getAuthority() !== null) {
                $result
                    ->setUserInfo($reference->getUserInfo())
                    ->setHost($reference->getHost())
                    ->setPort($reference->getPort())
                    ->setPath(self::removeDotSegments($reference->getPath()))
                    ->setQuery($reference->getQuery());
            } else {
                $result
                    ->setUserInfo($this->getUserInfo())
                    ->setHost($this->getHost())
                    ->setPort($this->getPort());

                $path = $reference->getPath();

                if (!$path) {
                    $result
                        ->setPath($this->getPath())
                        ->setQuery(
                            $reference->getQuery() !== null
                                ? $reference->getQuery()
                                : $this->getQuery()
                        );
                } else {
                    $result->setQuery($reference->getQuery());

                    if ($path[0] == '/') {
                        $result->setPath($path);
                    } else {
                        $result->setPath(
                            self::removeDotSegments(
                                self::mergePath($reference->getPath())
                            )
                        );
                    }
                }
            }
        }

        $result->setFragment($reference->getFragment());

        return $result;
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @return GenericUri
     **/
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;

        return $this;
    }

    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * @return GenericUri
     **/
    public function setUserInfo($userInfo)
    {
        $this->userInfo = $userInfo;

        return $this;
    }

    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return GenericUri
     **/
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return GenericUri
     **/
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    private static function removeDotSegments($path)
    {
        $segments = [];

        while ($path) {
            if (strpos($path, '../') === 0) {
                $path = substr($path, 3);

            } elseif (strpos($path, './') === 0) {
                $path = substr($path, 2);

            } elseif (strpos($path, '/./') === 0) {
                $path = substr($path, 2);

            } elseif ($path == '/.') {
                $path = '/';

            } elseif (strpos($path, '/../') === 0) {
                $path = substr($path, 3);

                if ($segments) {
                    array_pop($segments);
                }

            } elseif ($path == '/..') {
                $path = '/';

                if ($segments) {
                    array_pop($segments);
                }

            } elseif (($path == '..') || ($path == '.')) {
                $path = null;

            } else {
                $i = 0;

                if ($path[0] == '/') {
                    $i = 1;
                }

                $i = strpos($path, '/', $i);

                if ($i === false) {
                    $i = strlen($path);
                }

                $segments[] = substr($path, 0, $i);

                $path = substr($path, $i);
            }
        }

        return implode('', $segments);
    }

    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return GenericUri
     **/
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return GenericUri
     **/
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    public function getAuthority()
    {
        $result = null;

        if ($this->userInfo !== null) {
            $result .= $this->userInfo . '@';
        }

        if ($this->host !== null) {
            $result .= $this->host;
        }

        if ($this->port !== null) {
            $result .= ':' . $this->port;
        }

        return $result;
    }

    private function mergePath($path)
    {
        if ($this->getAuthority() !== null && !$this->getPath()) {
            return '/' . $path;
        }

        $segments = explode('/', $this->path);

        array_pop($segments);

        return implode('/', $segments) . '/' . $path;
    }

    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * @return GenericUri
     **/
    public function setFragment($fragment)
    {
        $this->fragment = $fragment;

        return $this;
    }

    /**
     * @return GenericUri
     **/
    public function appendQuery($string, $separator = '&')
    {
        $query = $this->query;

        if ($query) {
            $query .= $separator;
        }

        $query .= $string;

        $this->setQuery($query);

        return $this;
    }

    public function setSchemeSpecificPart($schemeSpecificPart)
    {
        throw new UnsupportedMethodException('use parse() instead');
    }

    /**
     * @return string
     */
    public function toString() : string
    {
        $result = '';

        if ($this->scheme !== null) {
            $result .= $this->scheme . ':';
        }

        $result .= $this->getSchemeSpecificPart();

        return $result;
    }

    /**
     * @return null|string
     */
    public function getSchemeSpecificPart()
    {
        $result = null;

        $authority = $this->getAuthority();

        if ($authority !== null) {
            $result .= '//' . $authority;
        }

        $result .= $this->path;

        if ($this->query !== null) {
            $result .= '?' . $this->query;
        }

        if ($this->fragment !== null) {
            $result .= '#' . $this->fragment;
        }

        return $result;
    }

    public function toStringFromRoot()
    {
        $result = $this->path;

        if ($this->query !== null) {
            $result .= '?' . $this->query;
        }

        if ($this->fragment !== null) {
            $result .= '#' . $this->fragment;
        }

        return $result;
    }

    public function isValid()
    {
        return
            $this->isValidScheme()
            && $this->isValidUserInfo()
            && $this->isValidHost()
            && $this->isValidPort()
            && $this->isValidPath()
            && $this->isValidQuery()
            && $this->isValidFragment();
    }

    public function isValidScheme()
    {
        // empty string is NOT valid
        return (
            $this->scheme === null
            || preg_match('~^[a-z][-+.a-z0-9]*$~i', $this->scheme) == 1
        );
    }

    public function isValidUserInfo()
    {
        // empty string IS valid
        if (!$this->userInfo) {
            return true;
        }

        $charPattern = $this->userInfoCharPattern();

        return (preg_match("/^$charPattern*$/i", $this->userInfo) == 1);
    }

    protected function userInfoCharPattern($pctEncoded = true)
    {
        return $this->charPattern(':', $pctEncoded);
    }

    protected function charPattern(
        $extraChars = null,
        $pctEncodedPattern = true
    ) {
        $unreserved = self::CHARS_UNRESERVED;
        $subDelims = self::CHARS_SUBDELIMS;
        $pctEncoded = self::PATTERN_PCTENCODED;

        $result = "{$unreserved}{$subDelims}$extraChars";

        if ($pctEncodedPattern) {
            $result = "(([{$result}])|({$pctEncoded}))";
        }

        return $result;
    }

    public function isValidHost()
    {
        // empty string IS valid
        if (empty($this->host)) {
            return true;
        }

        $decOctet =
            '(\d)|'            // 0-9
            . '([1-9]\d)|'    // 10-99
            . '(1\d\d)|'        // 100-199
            . '(2[0-4]\d)|'    // 200-249
            . '(25[0-5])';    // 250-255

        $ipV4Address = "($decOctet)\.($decOctet)\.($decOctet)\.($decOctet)";

        $hexdig = '[0-9a-f]';

        $h16 = "$hexdig{1,4}";
        $ls32 = "(($h16:$h16)|($ipV4Address))";

        $ipV6Address =
            "  (                        ($h16:){6} $ls32)"
            . "|(                      ::($h16:){5} $ls32)"
            . "|(              ($h16)? ::($h16:){4} $ls32)"
            . "|( (($h16:){0,1} $h16)? ::($h16:){3} $ls32)"
            . "|( (($h16:){0,2} $h16)? ::($h16:){2} $ls32)"
            . "|( (($h16:){0,3} $h16)? :: $h16:     $ls32)"
            . "|( (($h16:){0,4} $h16)? ::           $ls32)"
            . "|( (($h16:){0,5} $h16)? ::           $h16 )"
            . "|( (($h16:){0,6} $h16)? ::                )";

        $unreserved = self::CHARS_UNRESERVED;
        $subDelims = self::CHARS_SUBDELIMS;

        $ipVFutureAddress =
            "v$hexdig+\.[{$unreserved}{$subDelims}:]+";

        if (
        preg_match(
            "/^\[(($ipV6Address)|($ipVFutureAddress))\]$/ix",
            $this->host
        )
        ) {
            return true;
        }

        if (preg_match("/^$ipV4Address$/i", $this->host)) {
            return true;
        }

        return $this->isValidHostName();
    }

    protected function isValidHostName()
    {
        $charPattern = $this->hostNameCharPattern();

        return (
            preg_match(
                "/^$charPattern*$/i",
                $this->host
            ) == 1
        );
    }

    protected function hostNameCharPattern($pctEncoded = true)
    {
        return $this->charPattern(null, $pctEncoded);
    }

    public function isValidPort()
    {
        // empty string IS valid
        if (!$this->port) {
            return true;
        }

        if (!preg_match('~^\d*$~', $this->port)) {
            return false;
        }

        return ($this->port > 0 && $this->port <= 65535);
    }

    public function isValidPath()
    {
        $charPattern = $this->segmentCharPattern();

        if (
        !preg_match(
            "/^($charPattern+)?"
            . "(\/$charPattern*)*$/i",
            $this->path
        )
        ) {
            return false;
        }

        if ($this->getAuthority() !== null) {
            // abempty
            if (empty($this->path) || $this->path[0] == '/') {
                return true;
            }

        } elseif ($this->path && $this->path[0] == '/') {
            // absolute
            if ($this->path == '/' || $this->path[1] != '/') {
                return true;
            }

        } elseif ($this->scheme === null && $this->path) {
            // noscheme - first segment must be w/o colon

            $segments = explode('/', $this->path);

            if (strpos($segments[0], ':') === false) {
                return true;
            }

        } elseif ($this->path) {
            // rootless
            if ($this->path[0] != '/') {
                return true;
            }

        } elseif (!$this->path) {
            // empty
            return true;
        }

        return false;
    }

    protected function segmentCharPattern($pctEncoded = true)
    {
        return $this->charPattern(':@', $pctEncoded);
    }

    public function isValidQuery()
    {
        // empty string IS valid
        return $this->isValidFragmentOrQuery($this->query);
    }

    private function isValidFragmentOrQuery($string)
    {
        $charPattern = $this->fragmentOrQueryCharPattern();

        return (preg_match("/^$charPattern*$/i", $string) == 1);
    }

    protected function fragmentOrQueryCharPattern($pctEncoded = true)
    {
        return $this->charPattern(':@\/?', $pctEncoded);
    }

    public function isValidFragment()
    {
        // empty string IS valid
        return $this->isValidFragmentOrQuery($this->fragment);
    }

    public function isAbsolute()
    {
        return ($this->scheme !== null);
    }

    public function isRelative()
    {
        return ($this->scheme === null);
    }

    /**
     * @see http://tools.ietf.org/html/rfc3986#section-6
     **/
    public function normalize()
    {
        // 1. case
        if ($this->getScheme() !== null) {
            $this->setScheme(mb_strtolower($this->getScheme()));
        }

        // 2. percent-encoded
        $this
            ->setHost(
                $this->normalizePercentEncoded(
                    $this->getHost(), $this->hostNameCharPattern(false)
                )
            )
            ->setUserInfo(
                $this->normalizePercentEncoded(
                    $this->getUserInfo(), $this->userInfoCharPattern(false)
                )
            )
            ->setPath(
                self::removeDotSegments(
                    $this->normalizePercentEncoded(
                        $this->getPath(),
                        '\/' . $this->segmentCharPattern(false)
                    )
                )
            )
            ->setQuery(
                $this->normalizePercentEncoded(
                    $this->getQuery(),
                    $this->fragmentOrQueryCharPattern(false)
                )
            )
            ->setFragment(
                $this->normalizePercentEncoded(
                    $this->getFragment(),
                    $this->fragmentOrQueryCharPattern(false)
                )
            );

        // 3. and case again
        if ($this->getHost() !== null) {
            $this->setHost(mb_strtolower($this->getHost()));
        }

        return $this;
    }

    private function normalizePercentEncoded(
        $string,
        $unreservedPartChars
    ) {
        if ($string === null) {
            return null;
        }

        $result = preg_replace_callback(
            '/((' . self::PATTERN_PCTENCODED . ')|(.))/sui',
            [
                (new PercentEncodingNormalizator())->setUnreservedPartChars($unreservedPartChars),
                'normalize'
            ],
            $string
        );

        return $result;
    }
}

/**
 * @ingroup Net
 **/
class PercentEncodingNormalizator
{
    private $unreservedPartChars = null;

    /**
     * @return PercentEncodingNormalizator
     **/
    public function setUnreservedPartChars($unreservedPartChars)
    {
        $this->unreservedPartChars = $unreservedPartChars;
        return $this;
    }

    public function normalize($matched)
    {
        $char = $matched[0];
        if (mb_strlen($char) == 1) {
            if (
            !preg_match(
                '/^[' . $this->unreservedPartChars . ']$/u',
                $char
            )
            ) {
                $char = rawurlencode($char);
            }
        } else {
            if (
            preg_match(
                '/^[' . GenericUri::CHARS_UNRESERVED . ']$/u',
                rawurldecode($char)
            )
            ) {
                $char = rawurldecode($char);
            } else {
                $char = strtoupper($char);
            }
        }
        return $char;
    }
}


