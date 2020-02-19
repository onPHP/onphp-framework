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
	class FeedItem
	{
		private $id			= null;
		private $title		= null;
		private $content	= null;
		private $summary	= null;
		private $published	= null;
		private $link		= null;
		private $category	= null;
		
		/**
		 * @return FeedItem
		**/
		public static function create($title)
		{
			return new self($title);
		}
		
		public function __construct($title)
		{
			$this->title = $title;
		}
		
		public function getId()
		{
			return $this->id;
		}
		
		/**
		 * @return FeedItem
		**/
		public function setId($id)
		{
			$this->id = $id;
			
			return $this;
		}
		
		public function getTitle()
		{
			return $this->title;
		}
		
		/**
		 * @return FeedItem
		**/
		public function setTitle($title)
		{
			$this->title = $title;
			
			return $this;
		}
		
		public function getContent()
		{
			return $this->content;
		}
		
		/**
		 * @return FeedItem
		**/
		public function setContent($content)
		{
			$this->content = $content;
			
			return $this;
		}
		
		public function getSummary()
		{
			return $this->summary;
		}
		
		/**
		 * @return FeedItem
		**/
		public function setSummary($summary)
		{
			$this->summary = $summary;
			
			return $this;
		}
		
		/**
		 * @return Timestamp
		**/
		public function getPublished()
		{
			return $this->published;
		}
		
		/**
		 * @return FeedItem
		**/
		public function setPublished(Timestamp $published)
		{
			$this->published = $published;
			
			return $this;
		}
		
		public function getLink()
		{
			return $this->link;
		}
		
		/**
		 * @return FeedItem
		**/
		public function setLink($link)
		{
			$this->link = $link;
			
			return $this;
		}
		
		public function getCategory()
		{
			return $this->category;
		}
		
		/**
		 * @return FeedItem
		**/
		public function setCategory($category)
		{
			$this->category = $category;
			
			return $this;
		}
	}
?>