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
		public function render($model = null)
		{
			Assert::isTrue($model === null || $model instanceof Model);
			
			if (!$model || !$model->getList()) {
				if (!HeaderUtils::redirectBack())
					HeaderUtils::redirectRaw(
						PATH_WEB.'?area='.DEFAULT_MODULE
					);
			} else {
				$qs = array();
				
				foreach ($model->getList() as $key => $val)
					$qs[] = "{$key}={$val}";
				
				$url =
					(defined('ADMIN_AREA')
						? PATH_WEB_ADMIN
						: PATH_WEB)
					.'?'
					.implode('&', $qs);
				
				var_dump($url); die();
				
				header("Location: {$url}");
			}
		}
	}
?>