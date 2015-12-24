<?php
/***************************************************************************
 *   Copyright (C) 2007-2009 by Ivan Y. Khvostishkov                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * TODO: hierarchical scopes,
 * not only path/query - subdomains may be involved too,
 * ex: username.example.com
 **/
class ApplicationUrl
{
    protected $base = null;

    protected $applicationScope = array();
    protected $userScope = array();
    protected $navigationScope = array();

    protected $argSeparator = null;

    protected $navigationSchema = null;

    protected $absolute = false;

    /**
     * @deprecated
     *
     * @return ApplicationUrl
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @return HttpUrl
     **/
    public function getBase()
    {
        return $this->base;
    }

    /**
     * @return ApplicationUrl
     **/
    public function setBase(HttpUrl $base)
    {
        $this->base = $base;

        return $this;
    }

    public function isAbsolute()
    {
        return $this->absolute;
    }

    /**
     * @return ApplicationUrl
     **/
    public function setAbsolute($absolute)
    {
        $this->absolute = $absolute;

        return $this;
    }

    /**
     * @return ScopeNavigationSchema
     **/
    public function getNavigationSchema()
    {
        return $this->navigationSchema;
    }

    /**
     * @return ApplicationUrl
     **/
    public function setNavigationSchema(ScopeNavigationSchema $schema)
    {
        $this->navigationSchema = $schema;

        return $this;
    }

    /**
     * @return ApplicationUrl
     **/
    public function addApplicationScope($scope)
    {
        Assert::isArray($scope);

        $this->applicationScope = ArrayUtils::mergeRecursiveUnique(
            $this->applicationScope, $scope
        );

        return $this;
    }

    /**
     * @return ApplicationUrl
     **/
    public function addUserScope($userScope)
    {
        Assert::isArray($userScope);

        $this->userScope = ArrayUtils::mergeRecursiveUnique(
            $this->userScope, $userScope
        );

        return $this;
    }

    /**
     * @return ApplicationUrl
     **/
    public function dropFromUserScope($key)
    {
        if (isset($this->userScope[$key]))
            $this->userScope[$key] = null;

        return $this;
    }

    public function getUserScope()
    {
        return $this->userScope;
    }

    /**
     * @return ApplicationUrl
     **/
    public function setPathByRequestUri($requestUri, $normalize = true)
    {
        if (!$this->base)
            throw new WrongStateException(
                'base url must be set first'
            );

        $currentUrl = (new GenericUri)->parse($requestUri);

        if (!$currentUrl->isValid())
            throw new WrongArgumentException(
                'wtf? request uri is invalid'
            );

        if ($normalize)
            $currentUrl->normalize();

        $path = $currentUrl->getPath();

        // paranoia
        if (!$path || ($path[0] !== '/'))
            $path = '/' . $path;

        if (strpos($path, $this->base->getPath()) !== 0)
            throw new WrongArgumentException(
                'left parts of path and base url does not match: '
                . "$path vs. " . $this->base->getPath()
            );

        $actualPath = substr($path, strlen($this->base->getPath()));

        return $this->setPath($actualPath);
    }

    /**
     * @return ApplicationUrl
     **/
    public function setPath($path)
    {
        if (!$this->navigationSchema)
            throw new WrongStateException(
                'charly says always set navigation schema'
                . ' before you go off somewhere'
            );

        $scope = $this->navigationSchema->getScope($path);

        if ($scope === null)
            throw new WrongArgumentException(
                '404: not found'
            );

        $this->navigationScope = $scope;

        return $this;
    }

    public function getNavigationScope()
    {
        return $this->navigationScope;
    }

    public function currentHref(
        $additionalScope = array(),
        $absolute = null
    )
    {
        return $this->scopeHref(
            ArrayUtils::mergeRecursiveUnique(
                $this->userScope, $additionalScope
            ),
            $absolute
        );
    }

    public function scopeHref($scope, $absolute = null)
    {
        Assert::isArray($scope);

        // href scope may override navigation scope
        $actualScope = ArrayUtils::mergeRecursiveUnique(
            $this->navigationScope, $scope
        );

        return $this->cleanHref($actualScope, $absolute);
    }

    public function cleanHref($scope, $absolute = null)
    {
        Assert::isArray($scope);

        $path = $this->navigationSchema
            ? $this->navigationSchema->extractPath($scope)
            : null;

        return $this->href($path . '?' . $this->buildQuery($scope), $absolute);
    }

    public function href($url, $absolute = null)
    {
        if ($absolute === null)
            $absolute = $this->absolute;

        $result = $this->poorReference($url);

        if ($this->applicationScope)
            $result->appendQuery(
                $this->buildQuery($this->applicationScope),
                $this->getArgSeparator()
            );

        $result->normalize();

        if ($result->getQuery() === '')
            $result->setQuery(null);

        if ($absolute)
            return $result->toString();
        else
            return $result->toStringFromRoot();
    }

    public function poorReference($url)
    {
        Assert::isNotNull($this->base, 'set base url first');

        $parsedUrl = (new HttpUrl())->parse($url);

        return $this->base->transform($parsedUrl);
    }

    protected function buildQuery($scope)
    {
        return http_build_query(
            $scope, null, $this->getArgSeparator()
        );
    }

    public function getArgSeparator()
    {
        if (!$this->argSeparator)
            return ini_get('arg_separator.output');
        else
            return $this->argSeparator;
    }

    /**
     * @return ApplicationUrl
     **/
    public function setArgSeparator($argSeparator)
    {
        $this->argSeparator = $argSeparator;

        return $this;
    }

    public function baseHref($absolute = null)
    {
        return $this->href(null, $absolute);
    }

    public function absoluteHref($url)
    {
        return $this->href($url, true);
    }

    public function getUserQueryVars()
    {
        return $this->getQueryVars($this->userScope);
    }

    protected function getQueryVars($scope)
    {
        $queryParts = explode(
            $this->getArgSeparator(),
            $this->buildQuery($scope)
        );

        $result = array();

        foreach ($queryParts as $queryPart) {
            if (!$queryPart)
                continue;

            list($key, $value) = explode('=', $queryPart, 2);

            $result[$key] = $value;
        }

        return $result;
    }

    public function getApplicationQueryVars()
    {
        return $this->getQueryVars($this->applicationScope);
    }
}