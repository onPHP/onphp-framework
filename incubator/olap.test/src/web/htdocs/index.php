<?php
/***************************************************************************
 *   Copyright (C) 2006 by Ivan Khvostishkov                               *
 *   dedmajor@oemdesign.ru                                                 *
 ***************************************************************************/
/* $Id$ */

	ini_set('arg_separator.output', '&amp;');

	require '../../../config.inc.php';
	
	try {
		PackageManager::me()->
			import('onphp.incubator.Analytics');

		Application::me()->setupIncludePaths();

		Application::me()->resideInWeb();
		
		Application::me()->setActualDomain($_SERVER['SERVER_NAME']);
		Application::me()->navigate($_SERVER['REQUEST_URI']);
		
		$controller = Application::me()->getController('main');

		$modelAndView = $controller->handleRequest(
			HttpRequest::create()->
			setGet($_GET)->
			setPost($_POST)->
			setCookie($_COOKIE)->
			setServer($_SERVER)->
			setSession($_SESSION)
		);
		
		$view = $modelAndView->getView();
		$model = $modelAndView->getModel();
		
		if (!$view)
			$view = Application::me()->getNavigationArea()->getName();
		elseif (is_string($view) && $view == 'error')
			$view = new RedirectView(Application::me()->baseUrl());
		elseif ($view instanceof RedirectToView)
			$view->setPrefix(Application::me()->baseUrl());
			
		if (!$view instanceof View) {
			$viewName = $view;
			
			Application::me()->setMarkup(MarkupLanguage::html());
			
			$viewResolver =
				MultiPrefixPhpViewResolver::create()->
				setViewClassName('SimplePhpView');

			Application::me()->setupViewResolver($viewResolver);

			$view = $viewResolver->resolveViewName($viewName);
		}
		
		if (!$view instanceof RedirectView) {
			
			Application::me()->setImgStoragePath('img/');
				
			Application::me()->
				getImgStorage()->
					setExtensionsList(array('.png', '.jpg', '.gif'))->
					setStrict(false);
			
			Application::me()->setCssStoragePath('css/');
			
			if (isset($_SERVER['HTTP_ACCEPT']))
				$model->set('httpAccept', $_SERVER['HTTP_ACCEPT']);
				
			if (!isset($_SERVER['HTTP_USER_AGENT']))
				$httpUserAgent = null;
			else
				$httpUserAgent = $_SERVER['HTTP_USER_AGENT'];
				
			$model->set('httpUserAgent', $httpUserAgent);
		}
		
		$view->render($model);

	} catch (Exception $e) {
		
		$uri = $_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
		
		$msg =
			'class: '.get_class($e)."\n"
			.'code: '.$e->getCode()."\n"
			.'message: '.$e->getMessage()."\n\n"
			.$e->getTraceAsString()."\n"
			."\n_POST=".var_export($_POST, true)
			."\n_GET=".var_export($_GET, true)
			.(
				isset($_SERVER['HTTP_REFERER'])
					? "\nREFERER=".var_export($_SERVER['HTTP_REFERER'], true)
					: null
			)
			.(
				isset($_SESSION) ?
					"\n_SESSION=".var_export($_SESSION, true)
					: null
			);

		if (defined('__LOCAL_DEBUG__'))
			echo '<pre>'.$msg.'</pre>';
		else {
			mail(BUGLOVERS, $uri, $msg);
			sleep(10);
			if (!HeaderUtils::redirectBack())
				HeaderUtils::redirectRaw('/');
		}
	}
?>