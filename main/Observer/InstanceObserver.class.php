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
	
	final class InstanceObserver implements Observer
	{
		private $observerList = array();
		
		public function addCorrespondence(Observer $observer, /*Observable*/ $clazz)
		{
			Assert::isString($clazz);
			
			$this->observerList[$clazz] = $observer;
			
			return $this;
		}
		
		public function clearObservers()
		{
			$this->observerList = array();
			
			return $this;
		}
		
		public function handle(Observerable $observerable)
		{
			foreach ($this->observerList as $clazz => $observer)
				if ($observerable instanceof $clazz)
					$observer->handle($observerable);
		}
	}
?>