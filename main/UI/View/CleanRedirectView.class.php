<?php
/***************************************************************************
 *   Copyright (C) 2009 by Ivan Y. Khvostishkov                            *
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
class CleanRedirectView implements View
{
    protected $url = null;

    /**
     * CleanRedirectView constructor.
     * @param $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * @param null $model
     */
    public function render($model = null)
    {
        HeaderUtils::redirectRaw($this->getLocationUrl($model));
    }

    /**
     * @param null $model
     * @return null
     */
    protected function getLocationUrl($model = null)
    {
        return $this->getUrl();
    }

    /**
     * @return null
     */
    public function getUrl()
    {
        return $this->url;
    }
}
