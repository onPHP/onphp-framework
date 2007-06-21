<?php
/***************************************************************************
 *   Copyright (C) 2007 by Dmitry A. Lomash, Dmitry E. Demidov             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class RssChannelWorker extends Singleton implements FeedChannelWorker
	{
		/**
		 * @return RssChannelWorker
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return FeedChannel
		**/
		public function makeChannel(SimpleXMLElement $xmlFeed)
		{
			if (
				(!isset($xmlFeed->channel))
				|| (!isset($xmlFeed->channel->title))
			)
				throw new WrongStateException(
					'there are no channels in given rss'
				);
			
			$feedChannel =
				FeedChannel::create((string) $xmlFeed->channel->title);
			
			if (isset($xmlFeed->channel->link))
				$feedChannel->setLink((string) $xmlFeed->channel->link);
			
			return $feedChannel;
		}
		
		public function toXml(FeedChannel $channel, $itemsXml)
		{
			return
				'<rss version="'.RssFeedFormat::VERSION
					.'" xmlns:dc="http://purl.org/dc/elements/1.1/">'
					.'<channel>'
						.'<title>'.$channel->getTitle().'</title>'
						.(
							$channel->getLink()
								? '<link>'.$channel->getLink().'</link>'
								: null
						)
						.(
							$channel->getDescription()
								? 
									'<description>'
									.$channel->getDescription()
									.'</description>'
								: null
						)
						.$itemsXml
					.'</channel>'
				.'</rss>';
		}
	}
?>