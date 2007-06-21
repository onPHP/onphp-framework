<?php
/***************************************************************************
 *   Copyright (C) 2007 by Dmitry E. Pismenny, Dmitry A. Lomash            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class FeedReader
	{
		private $xml			= null;
		private $formats		= array();
		
		public function __construct()
		{
			$this->formats[] = AtomFeedFormat::me();
			$this->formats[] = RssFeedFormat::me();
		}
		
		public function isLoaded()
		{
			return ($this->xml) ? true : false;
		}
		
		/**
		 * @return FeedReader
		**/
		public function load($url)
		{
			$content = file_get_contents($url);

			return $this->loadXml(new SimpleXMLElement($content));
		}
		
		/**
		 * @return FeedReader
		**/
		public function loadFile($file)
		{
			return $this->loadXml(simplexml_load_file($file));
		}
		
		/**
		 * @return FeedReader
		**/
		public function loadXml(SimpleXMLElement $xml)
		{
			if ($this->isLoaded())
				throw new WrongStateException('Already loaded!');
			
			$this->xml = $xml;
			
			return $this;
		}
		
		/**
		 * @return FeedChannel
		**/
		public function parse()
		{
			foreach ($this->formats as $format)
				if ($format->isAcceptable($this->xml))
					return $format->parse($this->xml);
					
			return null;
		}
	}
?>