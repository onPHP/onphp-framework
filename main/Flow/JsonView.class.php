<?php
/***************************************************************************
 *   Copyright (C) 2011 by Dmitriy V. Snezhinskiy                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: JsonView.class.php 5489 2008-09-04 15:41:32Z sugrob $ */

	/**
	 * @ingroup Flow
	**/
	class JsonView extends JsonView
	{
		protected $options;

		/**
		 * @return JsonView
		**/
		public static function create($options = 0)
		{
			return new self($options);
		}
		
		public function __construct($options = 0)
		{
			$this->options = (int)$options;
		}

		/**
		 * @return JsonView
		**/
		public function render(/* Model */ $model = null)
		{
			if ($model instanceof Model) {
				echo json_encode($model->getList(), $this->options);
			}
			
			return $this;
		}

		public function toString($model = null)
		{
			ob_start();
			$this->render($model);
			return ob_get_clean();
		}

		/**
		 * @return JsonView
		**/
		protected function preRender()
		{
			return $this;
		}

		/**
		 * @return JsonView
		**/
		protected function postRender()
		{
			return $this;
		}
	}
?>