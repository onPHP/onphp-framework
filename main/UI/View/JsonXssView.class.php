<?php
/***************************************************************************
 *   Copyright (C) 2012 by Georgiy T. Kutsurua                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Flow
	**/

	class JsonXssView extends JsonPView
	{

		/**
		 * @param Model $model
		 * @return string
		 */
		public function toString(/* Model */ $model = null)
		{
			/*
			 * Escaping warning datas
			 */
			$this->setHexAmp(true);
			$this->setHexApos(true);
			$this->setHexQuot(true);
			$this->setHexTag(true);

			$jsonp = parent::toString($model);

			$jsonp = str_ireplace(
				array('u0022', 'u0027'),
				array('\u0022', '\u0027'),
				$jsonp
			);

			$result = '<script type="text/javascript">'."\n";
			$result.="\t".$jsonp."\n";
			$result.='</script>'."\n";

			return $result;
		}

	}
