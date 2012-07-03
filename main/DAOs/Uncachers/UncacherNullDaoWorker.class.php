<?php
/***************************************************************************
 *   Copyright (C) 2012 by Aleksey S. Denisov                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Uncachers
	**/
	class UncacherNullDaoWorker implements UncacherBase
	{
		public static function create()
		{
			return new self;
		}
		/**
		 * @param $uncacher UncacherNullDaoWorker same as self class
		 * @return BaseUncacher (this)
		 */
		public function merge(UncacherBase $uncacher)
		{
			Assert::isInstance($uncacher, 'UncacherNullDaoWorker');
			return $this;
		}
		
		public function uncache()
		{
			/* do nothing */
		}
	}
?>