<?php
/***************************************************************************
 *   Copyright (C) 2008 by Shimizu                                    *
 *   neemah86@gmail.com                                                    *
 ***************************************************************************/
/* $Id$ */

	class GoogleGeoPlacemarkIterator implements Countable, Iterator
	{
		protected $placemarks = null;
		protected $i = 0;

	    public function __construct(SimpleXMLElement $placemarks)
	    {
	    	$this->placemarks = $placemarks;
	    }
	
	    public function key()
	    {
	        return $this->i;
	    }
	
	    public function current()
	    {
	        return GoogleGeoPlaceMark::createFromSimpleXml($this->placemarks[$this->i]);
	    }
	
	    public function next()
	    {
	    	$this->i++;
	    	return $this;
	    }
	
	    public function rewind()
	    {
	    	$this->i = 0;
	    	return $this;
	    }
	    
	    public function count()
	    {
	    	return count($this->placemarks);
	    }
	    
	    public function valid()
	    {
	    	return isset($this->placemarks[$this->i]);
	    }
	}
?>