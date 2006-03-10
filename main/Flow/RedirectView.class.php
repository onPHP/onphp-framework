<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Flow
	**/
	final class RedirectView implements View
	{
		private $url = null;
		
		public function __construct($url) {
			$this->url = $url;
		}
		
		public function render ($model = null)
		{
			$postfix = '';
			if ($model && !$model->getList()) {
				$qs = array();
				foreach ($model->getList() as $key => $val)
					$qs[] = "{$key}={$val}";
				$postfix = '?'.implode('&', $qs);
			}
			HeaderUtils::redirectRaw($this->url.$postfix);
		}
	}
?>