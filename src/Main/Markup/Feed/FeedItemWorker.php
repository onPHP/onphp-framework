<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Markup\Feed;

use OnPHP\Core\Base\Instantiatable;

/**
 * @ingroup Feed
**/
interface FeedItemWorker extends Instantiatable
{
	public function makeItems(\SimpleXMLElement $xmlFeed);
	public function toXml(FeedItem $item);
}
?>