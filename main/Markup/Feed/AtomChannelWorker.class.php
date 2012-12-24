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
	final class AtomChannelWorker extends Singleton implements FeedChannelWorker
	{
		/**
		 * @return AtomChannelWorker
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
			$feedChannel = FeedChannel::create((string) $xmlFeed->title);
			
			if (isset($xmlFeed->link))
				if (is_array($xmlFeed->link))
					$feedChannel->setLink((string) $xmlFeed->link[0]);
				else
					$feedChannel->setLink((string) $xmlFeed->link);
			
			return $feedChannel;
		}
		
		public function toXml(FeedChannel $channel, $itemsXml)
		{
			throw new UnimplementedFeatureException('implement me!');
		}
	}
