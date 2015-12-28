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
 * @ingroup Html
 **/
final class SgmlIgnoredTag extends SgmlTag
{
    private $cdata = null;
    private $endMark = null;

    /**
     * @deprecated
     * @return SgmlIgnoredTag
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @return SgmlIgnoredTag
     **/
    public static function comment()
    {
        return (new self())->setId('!--')->setEndMark('--');
    }

    /**
     * @return Cdata
     **/
    public function getCdata()
    {
        return $this->cdata;
    }

    /**
     * @return SgmlIgnoredTag
     **/
    public function setCdata(Cdata $cdata)
    {
        $this->cdata = $cdata;

        return $this;
    }

    public function getEndMark()
    {
        return $this->endMark;
    }

    /**
     * @return SgmlIgnoredTag
     **/
    public function setEndMark($endMark)
    {
        $this->endMark = $endMark;

        return $this;
    }

    public function isComment()
    {
        return $this->getId() == '!--';
    }

    public function isExternal()
    {
        return ($this->getId() && $this->getId()[0] == '?');
    }
}

