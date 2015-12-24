<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * File uploads helper.
 *
 * @ingroup Primitives
 **/
class PrimitiveFile extends RangedPrimitive
{
    /** @var null */
    private $originalName = null;

    /** @var null */
    private $mimeType = null;

    /** @var array */
    private $allowedMimeTypes = [];
    /** @var bool */
    private $checkUploaded = true;

    /**
     * @return null
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * @return null
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @return PrimitiveFile
     **/
    public function clean()
    {
        $this->originalName = null;
        $this->mimeType = null;

        return parent::clean();
    }

    /**
     * @param $mime
     * @return PrimitiveFile
     * @throws WrongArgumentException
     */
    public function addAllowedMimeType($mime) : PrimitiveFile
    {
        Assert::isString($mime);

        $this->allowedMimeTypes[] = $mime;

        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedMimeTypes() : array
    {
        return $this->allowedMimeTypes;
    }


    /**
     * @param $mimes
     * @return PrimitiveFile
     * @throws WrongArgumentException
     */
    public function setAllowedMimeTypes($mimes) : PrimitiveFile
    {
        Assert::isArray($mimes);

        $this->allowedMimeTypes = $mimes;

        return $this;
    }

    /**
     * @param $path
     * @param $name
     * @return bool
     * @throws WrongArgumentException
     */
    public function copyTo($path, $name) : bool
    {
        return $this->copyToPath($path . $name);
    }

    /**
     * @param $path
     * @return bool
     * @throws WrongArgumentException
     */
    public function copyToPath($path) : bool
    {
        if (is_readable($this->value) && is_writable(dirname($path))) {
            if ($this->checkUploaded) {
                return move_uploaded_file($this->value, $path);
            } else {
                return rename($this->value, $path);
            }
        } else {
            throw new WrongArgumentException(
                "can not move '{$this->value}' to '{$path}'"
            );
        }
    }

    /**
     * @param $scope
     * @return bool|null
     */
    public function import($scope)
    {
        if (
            !BasePrimitive::import($scope)
            || !is_array($scope[$this->name])
            || (
                isset($scope[$this->name], $scope[$this->name]['error'])
                && $scope[$this->name]['error'] == UPLOAD_ERR_NO_FILE
            )
        ) {
            return null;
        }

        if (isset($scope[$this->name]['tmp_name'])) {
            $file = $scope[$this->name]['tmp_name'];
        } else {
            return false;
        }

        if (is_readable($file) && $this->checkUploaded($file)) {
            $size = filesize($file);
        } else {
            return false;
        }

        $this->mimeType = $scope[$this->name]['type'];

        if (!$this->isAllowedMimeType()) {
            return false;
        }

        if (
            isset($scope[$this->name])
            && !($this->max && ($size > $this->max))
            && !($this->min && ($size < $this->min))
        ) {
            $this->value = $scope[$this->name]['tmp_name'];
            $this->originalName = $scope[$this->name]['name'];

            return true;
        }

        return false;
    }

    /**
     * @param $file
     * @return bool
     */
    private function checkUploaded($file) : bool
    {
        return !$this->checkUploaded || is_uploaded_file($file);
    }

    /**
     * @return bool
     */
    public function isAllowedMimeType() : bool
    {
        if (count($this->allowedMimeTypes) > 0) {
            return in_array($this->mimeType, $this->allowedMimeTypes);
        } else {
            return true;
        }
    }

    /**
     * @throws UnimplementedFeatureException
     */
    public function exportValue()
    {
        throw new UnimplementedFeatureException();
    }

    /**
     * @return PrimitiveFile
     */
    public function enableCheckUploaded() : PrimitiveFile
    {
        $this->checkUploaded = true;

        return $this;
    }

    /**
     * @return PrimitiveFile
     */
    public function disableCheckUploaded() : PrimitiveFile
    {
        $this->checkUploaded = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCheckUploaded()
    {
        return $this->checkUploaded;
    }
}

?>