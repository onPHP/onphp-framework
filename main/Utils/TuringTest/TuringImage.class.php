<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Dmitry E. Demidov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Turing
 **/
class TuringImage
{
    /** @var ColorArray|null */
    private $textColors = null;

    /** @var ColorArray|null */
    private $backgroundColors = null;

    private $font = null;

    private $imageId = null;

    private $width = null;
    private $height = null;

    /** @var CodeGenerator|null */
    private $generator = null;

    /** @var TextDrawer|null */
    private $drawer = null;

    /** @var BackgroundDrawer|null */
    private $backgroundDrawer = null;

    private $code = null;


    /**
     * @param $width
     * @param $height
     */
    public function __construct($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }


    /**
     * @return TuringImage
     **/
    public function setGeneratedCode($code)
    {
        $this->code = $code;

        return $this;
    }

    public function getTextColors()
    {
        return $this->textColors;
    }

    /**
     * @param $textColors
     * @return TuringImage
     */
    public function setTextColors(ColorArray $textColors)
    {
        $this->textColors = $textColors;
        return $this;
    }

    public function getFont()
    {
        return $this->font;
    }

    /**
     * Set path and font
     * example: /srv/public_html/example.com/web/font/example.ttf
     *
     * @param $font
     * @return $this
     */
    public function setFont($font)
    {
        $this->font = $font;

        return $this;
    }

    /**
     * @return TuringImage
     **/
    public function setTextDrawer(TextDrawer $drawer)
    {
        $drawer->setTuringImage($this);
        $this->drawer = $drawer;

        return $this;
    }

    /**
     * @return TuringImage
     **/
    public function setBackgroundDrawer(BackgroundDrawer $drawer)
    {
        $drawer->setTuringImage($this);
        $this->backgroundDrawer = $drawer;

        return $this;
    }

    /**
     * @return CodeGenerator
     **/
    public function getCodeGenerator()
    {
        return $this->generator;
    }

    public function setCodeGenerator(CodeGenerator $generator)
    {
        $this->generator = $generator;
        return $this;
    }

    /**
     * @return int
     * @throws MissingElementException
     * @throws WrongStateException
     */
    public function getOneCharacterColor()
    {
        if (!$this->textColors instanceof ColorArray) {
            throw new MissingElementException('Please set the textColors');
        }

        $textColor = $this->textColors->getRandomTextColor();

        return $this->getColorIdentifier($textColor);
    }

    /**
     * @param Color $color
     * @return int
     */
    public function getColorIdentifier(Color $color)
    {
        $colorId =
            imagecolorexact(
                $this->imageId,
                $color->getRed(),
                $color->getGreen(),
                $color->getBlue()
            );

        if ($colorId === -1) {
            $colorId =
                imagecolorallocate(
                    $this->imageId,
                    $color->getRed(),
                    $color->getGreen(),
                    $color->getBlue()
                );
        }

        return $colorId;
    }

    /**
     * @param ImageType $imageType
     * @return TuringImage
     * @throws UnimplementedFeatureException
     * @throws WrongStateException
     */
    public function toImage(ImageType $imageType)
    {
        if ($this->drawer === null) {
            throw new WrongStateException('drawer must present');
        }

        $this->init();

        $this->drawBackGround();

        $this->drawer->draw($this->getGeneratedCode());

        $this->outputImage($imageType);

        imagedestroy($this->getImageId());

        return $this;
    }

    /**
     * @return TuringImage
     **/
    private function init()
    {
        $imageId = imagecreate($this->getWidth(), $this->getHeight());
        $this->imageId = $imageId;

        $this->getColorIdentifier(new Color('FFFFFF')); // white background

        return $this;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return $this
     * @throws MissingElementException
     */
    private function drawBackGround()
    {
        if (!$this->getBackgroundColors() instanceof ColorArray) {
            throw new MissingElementException('Please set the backgroundColors');
        }

        $backgroundColor = $this->backgroundColors->getRandomTextColor();
        $backgroundColorId = $this->getColorIdentifier($backgroundColor);

        imagefilledrectangle(
            $this->imageId,
            0,
            0,
            $this->getWidth(),
            $this->getHeight(),
            $backgroundColorId
        );

        if ($this->backgroundDrawer !== null) {
            $this->backgroundDrawer->draw();
        }

        return $this;
    }

    public function getBackgroundColors()
    {
        return $this->backgroundColors;
    }

    /**
     * @param ColorArray $backgroundColors
     * @return $this
     */
    public function setBackgroundColors(ColorArray $backgroundColors)
    {
        $this->backgroundColors = $backgroundColors;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getGeneratedCode()
    {
        if (!$this->code) {
            $this->code = $this->generator->generate();
        }

        return $this->code;
    }

    /**
     * @param ImageType $imageType
     * @return $this
     * @throws UnimplementedFeatureException
     */
    private function outputImage(ImageType $imageType)
    {
        switch ($imageType->getId()) {

            case ImageType::WBMP: {
                header("Content-type: image/vnd.wap.wbmp");
                imagewbmp($this->imageId);
                break;
            }

            case ImageType::PNG: {
                header("Content-type: image/png");
                imagepng($this->imageId);
                break;
            }

            case ImageType::JPEG: {
                header("Content-type: image/jpeg");
                imagejpeg($this->imageId);
                break;
            }

            case ImageType::GIF: {
                header("Content-type: image/gif");
                imagegif($this->imageId);
                break;
            }

            default:
                throw new UnimplementedFeatureException(
                    'requesting non-supported format'
                );
        }

        return $this;
    }

    public function getImageId()
    {
        return $this->imageId;
    }
}
