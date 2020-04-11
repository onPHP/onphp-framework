<?php
/***************************************************************************
 *   Copyright (C) 2010 by Alexandr S. Krotov                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Markup\Feed;

use OnPHP\Core\Base\Singleton;

/**
 * @ingroup Feed
 * @see http://partner.news.yandex.ru/tech.pdf
**/
final class YandexRssFeedFormat extends FeedFormat
{
	const  YANDEX_NAMESPACE_URI		= 'http://news.yandex.ru';
	const  YANDEX_NAMESPACE_PREFIX	= 'yandex';

	/**
	 * @return YandexRssFeedFormat
	**/
	public static function me()
	{
		return Singleton::getInstance(__CLASS__);
	}

	/**
	 * @return RssChannelWorker
	**/
	public function getChannelWorker()
	{
		return RssChannelWorker::me();
	}

	/**
	 * @return YandexRssItemWorker
	**/
	public function getItemWorker()
	{
		return YandexRssItemWorker::me();
	}

	public function isAcceptable(\SimpleXMLElement $xmlFeed)
	{
		return (
			($xmlFeed->getName() == 'rss')
			&& (isset($xmlFeed['version']))
			&& ($xmlFeed['version'] == RssFeedFormat::VERSION)
			&& array_key_exists(
				self::YANDEX_NAMESPACE_PREFIX,
				$xmlFeed->getDocNamespaces(true)
			)
		);
	}
}
?>