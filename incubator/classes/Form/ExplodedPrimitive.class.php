<?php
/*$Id$*/

class ExplodedPrimitive extends PrimitiveString
{
	protected $separator;
	
	public function setSeparator($separator)
	{
		$this->separator = $separator;
		
		return $this;
	}
	
	public function getSeparator()
	{
		return $this->separator;
	}
	
	public function import(&$scope)
	{
		if (!$temp = parent::import($scope))
			return $temp;

		if ($this->value = explode($this->separator, $this->value)) {
			return true;
		} else {
			return false;
		}
	}
}

/* Copyright 2005 Sveta Smirnova & Sergey Lasunov */
/*
* :tabSize=4:indentSize=4:noTabs=false:
* :folding=custom:collapseFolds=1:
*/
?>
