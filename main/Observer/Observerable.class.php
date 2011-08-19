<?php
/****************************************************************************
 *   Copyright (C) 2011 by Evgeny V. Kokovikhin                             *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	abstract class Observerable
	{
		private $observerList = array();
		
		public function addObserver(Observer $observer)
		{
			$this->observerList[] = $observer;
			
			return $this;
		}
		
		public function deleteObservers()
		{
			$this->observerList = array();
			
			return $this;
		}
		
		public function notify()
		{
			foreach ($this->observerList as $observer)
				$observer->handle($this);
			
			return $this;
		}
	}
?>