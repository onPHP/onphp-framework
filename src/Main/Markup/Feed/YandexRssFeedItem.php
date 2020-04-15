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

/**
 * @ingroup Feed
**/
final class YandexRssFeedItem extends FeedItem
{
	private $fullText	= null;

	/**
	 * @return YandexRssFeedItem
	**/
	public static function create($title)
	{
		return new self($title);
	}

	public function getFullText()
	{
		return $this->fullText;
	}

	/**
	 * @return YandexRssFeedItem
	**/
	public function setFullText($fullText)
	{
		$this->fullText = $fullText;

		return $this;
	}
}
?>