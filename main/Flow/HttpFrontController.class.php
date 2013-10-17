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
	abstract class HttpFrontController
	{
		/**
		 * @var HttpRequest
		 */
		protected $request = null;

		/**
		 * @var Controller
		 */
		protected $controller = null;

		/**
		 * @var ModelAndView
		 */
		protected $response = null;

		/**
		 * @var ViewResolver
		 */
		protected $resolver = null;

		/**
		 * @return Controller
		 */
		abstract protected function getController();

		public function __construct(ViewResolver $resolver)
		{
			$this->resolver = $resolver;
		}

		public function handleRequest(HttpRequest $request)
		{
			$this->request  = $request;
			$this->response = $this->getController()->handleRequest($request);

			$this->render()->terminateRequest();

			return $this;
		}

		protected function render()
		{
			if (is_string($this->response->getView())) {
				$this->response->setView(
					$this->resolver->resolveViewName(
						$this->response->getView()
					)
				);
			}

			$this->response->render();

			return $this;
		}

		protected function terminateRequest()
		{
			if (function_exists('fastcgi_finish_request'))
				fastcgi_finish_request();

			return $this;
		}
	}
?>
