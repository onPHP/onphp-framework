<?
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Khvostishkov                               *
 *   dedmajor@oemdesign.ru                                                 *
 ***************************************************************************/
/* $Id$ */

	final class XhtmlMpLanguage extends BaseMarkupLanguage
	{
		protected $commonName	= 'xhtmlmp';

		public function create()
		{
			return new self;
		}

		/**
		 * @returns XhtmlMpMarkupDocument
		**/
		public function parse($data)
		{
			return new UnimplementedFeatureException();
		}

		/**
		 * @returns string
		**/
		public function render(XhtmlMpMarkupDocument $data)
		{
			return new UnimplementedFeatureException();
		}
	}
?>