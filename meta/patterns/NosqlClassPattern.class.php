<?php
/***************************************************************************
 *	 Created by Alexey V. Gorbylev at 29.12.2011                           *
 *	 email: alex@gorbylev.ru, icq: 1079586, skype: avid40k                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Patterns
**/
class NosqlClassPattern extends BasePattern {

	public function tableExists()
	{
		return false;
	}

	public function daoExists()
	{
		return true;
	}

	/**
	 * @param MetaClass $class
	 * @return NosqlClassPattern
	 */
	protected function fullBuild(MetaClass $class)
	{
		return $this->
			buildProto($class)->
			buildBusiness($class)->
			buildNoSqlDao($class);
	}

}
