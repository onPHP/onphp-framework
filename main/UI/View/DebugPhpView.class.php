<?php
/***************************************************************************
 *   Copyright (C) 2007 by Denis M. Gabaidulin, Ivan Y. Khvostishkov       *
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
	namespace Onphp;

	final class DebugPhpView extends SimplePhpView
	{
		/**
		 * @return \Onphp\DebugPhpView
		**/
		public function preRender()
		{
			$trace = debug_backtrace();
			
			echo "<div style='margin:2px;padding:2px;border:1px solid #f00;'>";
			
			if (isset($trace[2])) {
				echo $trace[2]['file'] . ' (' . $trace[2]['line'] . '): ';
			}
			
			echo $this->templatePath;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\DebugPhpView
		**/
		protected function postRender()
		{
			echo "</div>";
			
			return $this;
		}
	}
?>