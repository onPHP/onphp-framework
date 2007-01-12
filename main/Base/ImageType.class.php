<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * PHP's image type constants.
	 * 
	 * @ingroup Helpers
	**/
	final class ImageType extends Enumeration
	{
		const GIF		= IMAGETYPE_GIF;
		const JPG		= IMG_JPG;
		const JPEG		= IMAGETYPE_JPEG;
		const PNG		= IMAGETYPE_PNG;
		const SWF		= IMAGETYPE_SWF;
		const PSD		= IMAGETYPE_PSD;
		const BMP		= IMAGETYPE_BMP;
		const TIFF_II	= IMAGETYPE_TIFF_II;
		const TIFF_MM	= IMAGETYPE_TIFF_MM;
		const JPC		= IMAGETYPE_JPC;
		const JP2		= IMAGETYPE_JP2;
		const JPX		= IMAGETYPE_JPX;
		const JB2		= IMAGETYPE_JB2;
		const SWC		= IMAGETYPE_SWC;
		const IFF		= IMAGETYPE_IFF;
		const WBMP		= IMAGETYPE_WBMP;
		const JPEG2000	= IMAGETYPE_JPEG2000;
		const XBM		= IMAGETYPE_XBM;
		
		protected $names = array(
			IMAGETYPE_GIF		=> 'gif',
			IMG_JPG				=> 'jpg',
			IMAGETYPE_JPEG		=> 'jpeg',
			IMAGETYPE_PNG		=> 'png',
			IMAGETYPE_SWF		=> 'swf',
			IMAGETYPE_PSD		=> 'psd',
			IMAGETYPE_BMP		=> 'bmp',
			IMAGETYPE_TIFF_II	=> 'tif',
			IMAGETYPE_TIFF_MM	=> 'tif',
			IMAGETYPE_JPC		=> 'jpc',
			IMAGETYPE_JP2		=> 'jp2',
			IMAGETYPE_JPX		=> 'jpx',
			IMAGETYPE_JB2		=> 'jb2',
			IMAGETYPE_SWC		=> 'swc',
			IMAGETYPE_IFF		=> 'iff',
			IMAGETYPE_WBMP		=> 'bmp',
			IMAGETYPE_JPEG2000	=> 'jpc',
			IMAGETYPE_XBM		=> 'xbm'
		);
		
		protected $mimeType		= null;
		
		protected $mimeTypes = array(
			IMAGETYPE_GIF		=> 'image/gif',
			IMG_JPG				=> 'image/jpg',
			IMAGETYPE_JPEG		=> 'image/jpeg',
			IMAGETYPE_PNG		=> 'image/png',
			IMAGETYPE_SWF		=> 'application/x-shockwave-flash',
			IMAGETYPE_PSD		=> 'application/octet-stream',
			IMAGETYPE_BMP		=> 'image/bmp',
			IMAGETYPE_TIFF_II	=> 'image/tiff',
			IMAGETYPE_TIFF_MM	=> 'image/tiff',
			IMAGETYPE_JPC		=> 'application/octet-stream',
			IMAGETYPE_JP2		=> 'image/jp2',
			IMAGETYPE_JPX		=> 'application/octet-stream',
			IMAGETYPE_JB2		=> 'application/octet-stream',
			IMAGETYPE_SWC		=> 'application/x-shockwave-flash',
			IMAGETYPE_IFF		=> 'image/iff',
			IMAGETYPE_WBMP		=> 'image/vnd.wap.wbmp',
			IMAGETYPE_JPEG2000	=> 'application/octet-stream',
			IMAGETYPE_XBM		=> 'image/xbm'
		);
		
		public function __construct($id)
		{
			parent::__construct($id);

			$this->mimeType = $this->mimeTypes[$id];
	
		}

		public function getMimeType()
		{
			return $this->mimeType;
		}
		
		public static function createByFileName($fileName)
		{
			$ext =
				pathinfo(
					strtolower($fileName), PATHINFO_EXTENSION
				);
				
			$anyImageType = new self(self::getAnyId());
			
			if ($id = array_search($ext, $anyImageType->getNameList()))
				return new self($id);
			else
				throw new WrongArgumentException("don't know type for '{$fileName}'");
		}
	}
?>