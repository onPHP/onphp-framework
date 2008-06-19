<?php
	/* $Id$ */
	
	final class ViewTest extends TestCase
	{
		public function testNullArgument()
		{
			$view = EmptyView::create();
			
			$nullModel = null;
			
			$view->render($nullModel);
		}
	}
?>