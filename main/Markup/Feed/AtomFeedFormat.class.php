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
	namespace Onphp;

	final class AtomFeedFormat extends FeedFormat
	{
		/**
		 * @return \Onphp\AtomFeedFormat
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return \Onphp\AtomChannelWorker
		**/
		public function getChannelWorker()
		{
			return AtomChannelWorker::me();
		}
		
		/**
		 * @return \Onphp\AtomItemWorker
		**/
		public function getItemWorker()
		{
			return AtomItemWorker::me();
		}
		
		public function isAcceptable(\SimpleXMLElement $xmlFeed)
		{
			return ($xmlFeed->getName() == 'feed');
		}
	}
?>