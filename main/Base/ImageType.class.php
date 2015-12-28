<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * PHP's image type constants.
 *
 * @ingroup Helpers
 **/
class ImageType extends Enumeration
{
    const IMAGETYPE_PJPEG = 100;
    const IMAGETYPE_SWC = 101;

    const GIF = IMAGETYPE_GIF;
    const JPEG = IMAGETYPE_JPEG;
    const PNG = IMAGETYPE_PNG;
    const SWF = IMAGETYPE_SWF;
    const PSD = IMAGETYPE_PSD;
    const BMP = IMAGETYPE_BMP;
    const TIFF_II = IMAGETYPE_TIFF_II;
    const TIFF_MM = IMAGETYPE_TIFF_MM;
    const JPC = IMAGETYPE_JPC;
    const JP2 = IMAGETYPE_JP2;
    const JPX = IMAGETYPE_JPX;
    const JB2 = IMAGETYPE_JB2;
    const SWC = self::IMAGETYPE_SWC;
    const IFF = IMAGETYPE_IFF;
    const WBMP = IMAGETYPE_WBMP;
    const JPEG2000 = IMAGETYPE_JPEG2000;
    const XBM = IMAGETYPE_XBM;
    const PJPEG = self::IMAGETYPE_PJPEG;

    protected $names = array(
        IMAGETYPE_GIF => 'gif',
        IMAGETYPE_JPEG => 'jpeg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_SWF => 'swf',
        IMAGETYPE_PSD => 'psd',
        IMAGETYPE_BMP => 'bmp',
        IMAGETYPE_TIFF_II => 'tif',
        IMAGETYPE_TIFF_MM => 'tif',
        IMAGETYPE_JPC => 'jpc',
        IMAGETYPE_JP2 => 'jp2',
        IMAGETYPE_JPX => 'jpx',
        IMAGETYPE_JB2 => 'jb2',
        self::IMAGETYPE_SWC => 'swc',
        IMAGETYPE_IFF => 'iff',
        IMAGETYPE_WBMP => 'bmp',
        IMAGETYPE_JPEG2000 => 'jpc',
        IMAGETYPE_XBM => 'xbm',
        self::IMAGETYPE_PJPEG => 'jpeg'
    );

    protected $extensions = array(
        'gif' => IMAGETYPE_GIF,
        'jpg' => IMAGETYPE_JPEG,
        'jpeg' => IMAGETYPE_JPEG,
        'pjpeg' => self::IMAGETYPE_PJPEG,
        'png' => IMAGETYPE_PNG,
        'swf' => IMAGETYPE_SWF,
        'psd' => IMAGETYPE_PSD,
        'bmp' => IMAGETYPE_BMP,
        'tif' => IMAGETYPE_TIFF_II,
        'tiff' => IMAGETYPE_TIFF_II,
        'jpc' => IMAGETYPE_JPC,
        'jp2' => IMAGETYPE_JP2,
        'jpx' => IMAGETYPE_JPX,
        'jb2' => IMAGETYPE_JB2,
        'swc' => self::IMAGETYPE_SWC,
        'iff' => IMAGETYPE_IFF,
        'wbmp' => IMAGETYPE_WBMP,
        'jpc' => IMAGETYPE_JPEG2000,
        'xbm' => IMAGETYPE_XBM
    );

    protected $mimeTypes = array(
        IMAGETYPE_GIF => 'image/gif',
        IMAGETYPE_JPEG => 'image/jpeg',
        IMAGETYPE_PNG => 'image/png',
        IMAGETYPE_SWF => 'application/x-shockwave-flash',
        IMAGETYPE_PSD => 'image/x-photoshop',
        IMAGETYPE_BMP => 'image/bmp',
        IMAGETYPE_TIFF_II => 'image/tiff',
        IMAGETYPE_TIFF_MM => 'image/tiff',
        IMAGETYPE_JPC => 'image/jpc',
        IMAGETYPE_JP2 => 'image/jp2',
        IMAGETYPE_JPX => 'image/jpx',
        IMAGETYPE_JB2 => 'image/jb2',
        self::IMAGETYPE_SWC => 'application/x-shockwave-flash',
        IMAGETYPE_IFF => 'image/iff',
        IMAGETYPE_WBMP => 'image/vnd.wap.wbmp',
        IMAGETYPE_JPEG2000 => 'image/jpeg',
        IMAGETYPE_XBM => 'image/xbm',
        self::IMAGETYPE_PJPEG => 'image/pjpeg'
    );

    public static function createByFileName($fileName)
    {
        $ext =
            strtolower(
                pathinfo($fileName, PATHINFO_EXTENSION)
            );

        $anyImageType = new self(self::getAnyId());
        $extensionList = $anyImageType->getExtensionList();

        if (isset($extensionList[$ext]))
            return new self($extensionList[$ext]);

        throw new WrongArgumentException(
            "don't know type for '{$ext}' extension"
        );
    }

    public static function getAnyId() : int
    {
        return self::GIF;
    }

    public function getExtensionList()
    {
        return $this->extensions;
    }

    public function getMimeType()
    {
        return $this->mimeTypes[$this->id];
    }

    public function getExtension()
    {
        $flippedExensions = array_flip($this->extensions);

        return $flippedExensions[$this->id];
    }
}