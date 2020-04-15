<?php
/***************************************************************************
 *   Copyright (C) 2007 by Dmitry A. Lomash                                *
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
final class FeedItemContent
{
	private $type = null;
	private $body = null;

	/**
	 * @return FeedItemContent
	**/
	public static function create()
	{
		return new self;
	}

	/**
	 * @return FeedItemContentType
	**/
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return FeedItemContent
	**/
	public function setType(FeedItemContentType $type)
	{
		$this->type = $type;

		return $this;
	}

	public function getBody()
	{
		return $this->body;
	}

	/**
	 * @return FeedItemContent
	**/
	public function setBody($body)
	{
		$this->body = $body;

		return $this;
	}
}
?>