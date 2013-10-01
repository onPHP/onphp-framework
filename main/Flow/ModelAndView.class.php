<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
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
	class ModelAndView implements HttpResponse
	{
		/**
		 * @var Model
		**/
		private $model 	          = null;

		/**
		 * @var View|string
		**/
		private $view	          = null;

		/**
		 * @var HttpStatus
		**/
		private $status           = null;

		/**
		 * @var HttpHeaderCollection
		**/
		private $headerCollection = null;

		/**
		 * @var CookieCollection
		**/
		private $cookieCollection = null;

		/**
		 * @var bool
		**/
		private $enabledContentLength = false;

		/**
		 * @return ModelAndView
		**/
		public static function create()
		{
			return new static();
		}
		
		public function __construct()
		{
			$this->model = new Model();
			$this->status = new HttpStatus(HttpStatus::CODE_200);
			$this->headerCollection = new HttpHeaderCollection();
			$this->cookieCollection = new CookieCollection();
		}
		
		/**
		 * @return Model
		**/
		public function getModel()
		{
			return $this->model;
		}
		
		/**
		 * @return ModelAndView
		**/
		public function setModel(Model $model)
		{
			$this->model = $model;
			
			return $this;
		}
		
		public function getView()
		{
			return $this->view;
		}
		
		/**
		 * @return ModelAndView
		**/
		public function setView($view)
		{
			Assert::isTrue(
				($view instanceof View)	|| is_string($view),
				'do not know, what to do with such view'
			);
			
			$this->view = $view;
			
			return $this;
		}

		/**
		 * @deprecated
		**/
		public function viewIsRedirect()
		{
			return
				($this->view instanceof CleanRedirectView)
				|| (
					is_string($this->view)
					&& strpos($this->view, 'redirect') === 0
				);
		}

		/**
		 * @deprecated
		**/
		public function viewIsNormal()
		{
			return (
				!$this->viewIsRedirect()
				&& $this->view !== View::ERROR_VIEW
			);
		}

		/**
		 * @return HttpHeaderCollection
		**/
		public function getHeaderCollection()
		{
			return $this->headerCollection;
		}

		/**
		 * @return CookieCollection
		**/
		public function getCookieCollection()
		{
			return $this->cookieCollection;
		}

		public function enableContentLength()
		{
			$this->enabledContentLength = true;

			return $this;
		}

		public function disableContentLength()
		{
			$this->enabledContentLength = false;

			return $this;
		}

		public function setStatus(HttpStatus $status)
		{
			$this->status = $status;

			return $this;
		}

		/**
		 * @return HttpStatus
		 **/
		public function getStatus()
		{
			return $this->status;
		}

		public function getReasonPhrase()
		{
			return $this->status->getName();
		}

		public function getHeaders()
		{
			return $this->headerCollection->getAll();
		}

		public function hasHeader($name)
		{
			return $this->headerCollection->has($name);
		}

		public function getHeader($name)
		{
			return $this->headerCollection->get($name);
		}

		/**
		 * @throws RuntimeException
		**/
		public function getBody()
		{
			if (!$this->view)
				return null;

			if (is_string($this->view)) {
				throw new RuntimeException(
					sprintf('View "%s" must be resolved', $this->view)
				);
			}

			ob_start();

			try {
				$this->view->render($this->model);
			} catch (Exception $e) {
				ob_end_clean();

				throw new RuntimeException(
					'Error while rendering view',
					(int) $e->getCode(),
					$e
				);
			}

			return ob_get_clean();
		}

		public function render()
		{
			if ($this->enabledContentLength) {
				$content = $this->getBody();
				$this->headerCollection->set('Content-Length', strlen($content));
				$this->sendHeaders();

				echo $content;
			} else {
				Assert::isInstance($this->view, 'View');

				$this->sendHeaders();
				$this->view->render($this->model);
			}

			return $this;
		}

		public function sendHeaders()
		{
			if (headers_sent($file, $line)) {
				throw new LogicException(
					sprintf('Headers are gone at %s:%d', $file, $line)
				);
			}

			header($this->status->toString());

			foreach ($this->headerCollection as $name => $valueList)
				foreach ($valueList as $value)
					header($name.': '.$value, true);

			$this->cookieCollection->httpSetAll();

			return $this;
		}
	}
?>