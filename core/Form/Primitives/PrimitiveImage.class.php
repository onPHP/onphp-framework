<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Image uploads helper.
 *
 * @ingroup Primitives
 **/
final class PrimitiveImage extends PrimitiveFile
{
    /** @var null  */
    private $width = null;
    /** @var null  */
    private $height = null;

    /** @var null  */
    private $maxWidth = null;
    /** @var null  */
    private $minWidth = null;

    /** @var null  */
    private $maxHeight = null;
    /** @var null  */
    private $minHeight = null;

    private $type = null;

    /**
     * clean sizes
     *
     * @return PrimitiveImage
     **/
    public function clean() : PrimitiveImage
    {
        $this->width = $this->height = null;

        $this->type = null;

        return parent::clean();
    }

    /**
     * @return null
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return null
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return null
     */
    public function getMaxWidth()
    {
        return $this->maxWidth;
    }

    /**
     * @param $max
     * @return PrimitiveImage
     */
    public function setMaxWidth($max) : PrimitiveImage
    {
        $this->maxWidth = $max;

        return $this;
    }

    /**
     * @return null
     */
    public function getMinWidth()
    {
        return $this->minWidth;
    }

    /**
     * @param $min
     * @return PrimitiveImage
     */
    public function setMinWidth($min) : PrimitiveImage
    {
        $this->minWidth = $min;

        return $this;
    }

    /**
     * @return null
     */
    public function getMaxHeight()
    {
        return $this->maxHeight;
    }

    /**
     * @param $max
     * @return PrimitiveImage
     */
    public function setMaxHeight($max) : PrimitiveImage
    {
        $this->maxHeight = $max;

        return $this;
    }

    /**
     * @return null
     */
    public function getMinHeight()
    {
        return $this->minHeight;
    }

    /**
     * @param $min
     * @return PrimitiveImage
     */
    public function setMinHeight($min) : PrimitiveImage
    {
        $this->minHeight = $min;

        return $this;
    }

    /**
     * @param $scope
     * @return bool|null
     */
    public function import($scope)
    {
        if (!$result = parent::import($scope)) {
            return $result;
        }

        try {
            list($width, $height, $type) = getimagesize($this->value);
        } catch (BaseException $e) {
            // bad luck
            return false;
        }

        if (!$width || !$height || !$type) {
            $this->value = null;
            return false;
        }

        if (
            !($this->maxWidth && ($width > $this->maxWidth))
            && !($this->minWidth && ($width < $this->minWidth))
            && !($this->maxHeight && ($height > $this->maxHeight))
            && !($this->minHeight && ($height < $this->minHeight))
        ) {
            $this->type = new ImageType($type);
            $this->width = $width;
            $this->height = $height;

            return true;
        }

        return false;
    }
}
