<?php
/***************************************************************************
 *   Copyright (C) 2007 by Dmitry E. Pismenny, Dmitry A. Lomash            *
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
class FeedReader
{
    private $xml = null;
    private $formats = [];

    public function __construct()
    {
        $this->formats[] = YandexRssFeedFormat::me();
        $this->formats[] = AtomFeedFormat::me();
        $this->formats[] = RssFeedFormat::me();
    }

    /**
     * @return FeedReader
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @return SimpleXMLElement
     **/
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * @return FeedChannel
     **/
    public function parseFile($file)
    {
        try {
            $this->xml = simplexml_load_file($file);
        } catch (BaseException $e) {
            throw new WrongArgumentException(
                'Invalid link or content: ' . $e->getMessage()
            );
        }

        if (!$this->xml) {
            throw new WrongStateException('simplexml_load_file failed.');
        }

        return $this->parse();
    }

    /**
     * @return FeedChannel
     **/
    private function parse()
    {
        foreach ($this->formats as $format) {
            if ($format->isAcceptable($this->xml)) {
                return $format->parse($this->xml);
            }
        }

        throw new WrongStateException(
            'you\'re using unsupported format of feed'
        );
    }

    /**
     * @return FeedReader
     **/
    public function parseXml($xml)
    {
        $this->xml = new SimpleXMLElement($xml);

        return $this->parse();
    }
}

