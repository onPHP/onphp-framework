<?php
/***************************************************************************
 *   Copyright (C) 2007 by Dmitry A. Lomash, Dmitry E. Demidov             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Feed
 **/
class FeedChannel
{
    private $title = null;
    private $link = null;
    private $description = null;
    private $feedItems = [];

    public function __construct($title)
    {
        $this->title = $title;
    }


    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return FeedChannel
     **/
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return FeedChannel
     **/
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return FeedChannel
     **/
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    public function getFeedItems()
    {
        return $this->feedItems;
    }

    /**
     * @return FeedChannel
     **/
    public function setFeedItems($feedItems)
    {
        $this->feedItems = $feedItems;

        return $this;
    }

    /**
     * @return FeedChannel
     **/
    public function addFeedItem(FeedItem $feedItem)
    {
        $this->feedItems[] = $feedItem;

        return $this;
    }
}

