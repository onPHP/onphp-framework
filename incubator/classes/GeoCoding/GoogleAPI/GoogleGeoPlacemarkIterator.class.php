<?php
/***************************************************************************
 *   Copyright (C) 2008 by Tsyrulnik Y. Viatcheslav                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class GoogleGeoPlacemarkIterator implements Countable, Iterator
	{
		protected $placemarks = null;
		
		protected $key = 0;
		
	    public function __construct(SimpleXMLElement $placemarks)
	    {
			$this->placemarks = $placemarks;
	    }
		
	    public function key()
	    {
	        return $this->key;
	    }
		
	    /**
	     * @return GoogleGeoPlaceMark
	    **/
	    public function current()
	    {
	        return GoogleGeoPlaceMark::createFromSimpleXml(
	        	$this->placemarks[$this->key]
	        );
	    }
		
	    /**
	     * @return GoogleGeoPlacemarkIterator
	    **/
	    public function next()
	    {
			++$this->key;
			return $this;
	    }
		
	    /**
	     * @return GoogleGeoPlacemarkIterator
	    **/
	    public function rewind()
	    {
			$this->key = 0;
			return $this;
	    }
		
	    public function count()
	    {
			return count($this->placemarks);
	    }
		
	    public function valid()
	    {
			return isset($this->placemarks[$this->key]);
	    }
	}
?>