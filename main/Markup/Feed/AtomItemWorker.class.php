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
final class AtomItemWorker extends Singleton implements FeedItemWorker
{
    /**
     * @return AtomItemWorker
     **/
    public static function me()
    {
        return Singleton::getInstance(__CLASS__);
    }

    public function makeItems(SimpleXMLElement $xmlFeed)
    {
        $result = [];

        foreach ($xmlFeed->entry as $entry) {
            $feedItem = new FeedItem((string) $entry->title);

            if (isset($entry->content)) {
                $feedItem->setContent(
                    $this->makeFeedItemContent(
                        $entry->content
                    )
                );
            }

            if (isset($entry->summary)) {
                $feedItem->setSummary(
                    $this->makeFeedItemContent(
                        $entry->summary
                    )
                );
            }

            if (isset($entry->id)) {
                $feedItem->setId(
                    $entry->id
                );
            }

            $result[] =
                $feedItem
                    ->setPublished(
                        new Timestamp(strtotime((string) $entry->updated))
                    )
                    ->setLink((string) $entry->link);
        }

        return $result;
    }

    private function makeFeedItemContent($content)
    {
        $feedItemContent = FeedItemContent::create();

        if (isset($content->attributes()->type)) {
            switch ((string) $content->attributes()->type) {

                case 'text':

                    $feedItemContent->
                    setType(
                        new FeedItemContentType(
                            FeedItemContentType::TEXT
                        )
                    );

                    break;

                case 'html':

                    $feedItemContent->
                    setType(
                        new FeedItemContentType(
                            FeedItemContentType::HTML
                        )
                    );

                    break;

                case 'xhtml':

                    $feedItemContent->
                    setType(
                        new FeedItemContentType(
                            FeedItemContentType::XHTML
                        )
                    );

                    break;
            }
        }

        return $feedItemContent->setBody((string) $content);
    }

    public function toXml(FeedItem $item)
    {
        throw new UnimplementedFeatureException('implement me!');
    }
}

