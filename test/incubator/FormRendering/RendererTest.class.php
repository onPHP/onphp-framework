<?php

	class RendererTest extends TestCase {
		public function testFactory()
		{
			// Is it correct?
			// @see Assert::brothers()
			$this->assertTrue(
				get_class(new HtmlRenderer()) == get_class(Renderer::html())
			);
		}

		/**
		 * @expectedException UnimplementedFeatureException
		 */
		public function testUnimplementedjQueryUI()
		{
			Renderer::jQueryUI();
		}

		/**
		 * @expectedException UnimplementedFeatureException
		 */
		public function testUnimplementeddojoUI()
		{
			Renderer::dojoUI();
		}

		/**
		 * @expectedException UnimplementedFeatureException
		 */
		public function testUnimplementedExtJS()
		{
			Renderer::ExtJS();
		}
	}

?>