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

	class RssFeedFormat extends FeedFormat
	{
		const VERSION = '2.0';
		
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		public function getChannelWorker()
		{
			return RssChannelWorker::me();
		}
		
		public function getItemWorker()
		{
			return RssItemWorker::me();
		}
		
		public function isAcceptable(SimpleXMLElement $xmlFeed)
		{
			return (
				($xmlFeed->getName() == 'rss')
				&& (isset($xmlFeed['version']))
				&& ($xmlFeed['version'] == self::VERSION)
			);
		}
	}
?>