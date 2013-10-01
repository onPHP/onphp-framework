<?php
/***************************************************************************
 *   Copyright (C) 2013 by Nikita V. Konstantinov                          *
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
	class RawView implements View
	{
		private $content = null;

		public function __construct($content)
		{
			$this->content = $content;
		}

		public function render($model = null)
		{
			echo $this->content;

			return $this;
		}
	}
?>
