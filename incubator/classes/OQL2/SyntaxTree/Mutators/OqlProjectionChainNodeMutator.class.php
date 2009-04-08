<?php
/****************************************************************************
 *   Copyright (C) 2009 by Vladlen Y. Koshelev                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * @ingroup OQL
	**/
	final class OqlProjectionChainNodeMutator extends OqlAbstractChainNodeMutator
	{
		/**
		 * @return OqlProjectionChainNodeMutator
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return OqlObjectProjectionNode
		**/
		protected function makeChainNode(array $list)
		{
			$chain = Projection::chain();
			foreach ($list as $projection)
				$chain->add($projection->toValue());
			
			return OqlObjectProjectionNode::create()->
				setObject($chain)->
				setList($list);
		}
	}
?>