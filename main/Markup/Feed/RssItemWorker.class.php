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
final class RssItemWorker extends Singleton implements FeedItemWorker
{
    /**
     * @return RssItemWorker
     **/
    public static function me()
    {
        return Singleton::getInstance(__CLASS__);
    }

    public function makeItems(SimpleXMLElement $xmlFeed)
    {
        $result = [];

        if (isset($xmlFeed->channel->item)) {
            foreach ($xmlFeed->channel->item as $item) {
                $feedItem =
                    (new FeedItem((string) $item->title))
                        ->setContent(
                            (new FeedItemContent())
                                ->setBody((string) $item->description)
                        )
                        ->setPublished(
                            new Timestamp(strtotime((string) $item->pubDate))
                        )
                        ->setLink((string) $item->link);

                if (isset($item->guid)) {
                    $feedItem->setId($item->guid);
                }

                if (isset($item->category)) {
                    $feedItem->setCategory((string) $item->category);
                }

                $result[] = $feedItem;
            }
        }

        return $result;
    }

    public function toXml(FeedItem $item)
    {
        return
            '<item>'
            . (
            $item->getPublished()
                ?
                '<pubDate>'
                . date('r', $item->getPublished()->toStamp())
                . '</pubDate>'
                : null
            )
            . (
            $item->getId()
                ?
                '<guid isPermaLink="false">'
                . $item->getId()
                . '</guid>'
                : null
            )
            . '<title>' . $item->getTitle() . '</title>'
            . (
            $item->getLink()
                ?
                '<link>'
                . str_replace("&", "&amp;", $item->getLink())
                . '</link>'
                : null
            )
            . (
            $item->getSummary()
                ? '<description>' . $item->getSummary() . '</description>'
                : null
            )
            . (
            $item->getCategory()
                ? '<category>' . $item->getCategory() . '</category>'
                : null
            )
            . '</item>';
    }
}

