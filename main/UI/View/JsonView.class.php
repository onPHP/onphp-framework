<?php
/***************************************************************************
 *   Copyright (C) 2011 by Dmitriy V. Snezhinskiy                          *
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
class JsonView implements View, Stringable
{
    protected $options = 0;

    /**
     * @return JsonView
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @param bool $flag
     * @return JsonView
     **/
    public function setHexQuot($flag = false)
    {
        if ($flag) {
            $this->options = $this->options | JSON_HEX_QUOT;
        } else {
            $this->options = $this->options & ~JSON_HEX_QUOT;
        }

        return $this;
    }

    /**
     * @param bool $flag
     * @return JsonView
     **/
    public function setHexTag($flag = false)
    {
        if ($flag) {
            $this->options = $this->options | JSON_HEX_TAG;
        } else {
            $this->options = $this->options & ~JSON_HEX_TAG;
        }

        return $this;
    }

    /**
     * @param bool $flag
     * @return JsonView
     **/
    public function setHexAmp($flag = false)
    {
        if ($flag) {
            $this->options = $this->options | JSON_HEX_AMP;
        } else {
            $this->options = $this->options & ~JSON_HEX_AMP;
        }

        return $this;
    }

    /**
     * @param bool $flag
     * @return JsonView
     **/
    public function setHexApos($flag = false)
    {
        if ($flag) {
            $this->options = $this->options | JSON_HEX_APOS;
        } else {
            $this->options = $this->options & ~JSON_HEX_APOS;
        }

        return $this;
    }

    /**
     * @param bool $flag
     * @return JsonView
     **/
    public function setForceObject($flag = false)
    {
        if ($flag) {
            $this->options = $this->options | JSON_FORCE_OBJECT;
        } else {
            $this->options = $this->options & ~JSON_FORCE_OBJECT;
        }

        return $this;
    }

    /**
     * @param bool $flag
     * @return JsonView
     **/
    public function setNumericCheck($flag = false)
    {
        if ($flag) {
            $this->options = $this->options | JSON_NUMERIC_CHECK;
        } else {
            $this->options = $this->options & ~JSON_NUMERIC_CHECK;
        }

        return $this;
    }

    /**
     * @param bool $flag
     * @return JsonView
     **/
    public function setPrettyPrint($flag = false)
    {
        if (defined("JSON_PRETTY_PRINT")) {
            if ($flag) {
                $this->options = $this->options | JSON_PRETTY_PRINT;
            } else {
                $this->options = $this->options & ~JSON_PRETTY_PRINT;
            }
        }

        return $this;
    }

    /**
     * @param bool $flag
     * @return JsonView
     **/
    public function setUnescapedSlashes($flag = false)
    {
        if (defined("JSON_UNESCAPED_SLASHES")) {
            if ($flag) {
                $this->options = $this->options | JSON_UNESCAPED_SLASHES;
            } else {
                $this->options = $this->options & ~JSON_UNESCAPED_SLASHES;
            }
        }

        return $this;
    }

    /**
     * @return JsonView
     **/
    public function render(/* Model */
        $model = null)
    {
        echo $this->toString($model);

        return $this;
    }

    /**
     * @param Model $model
     * @return string
     */
    public function toString(/* Model */
        $model = null)
    {
        Assert::isTrue($model === null || $model instanceof Model);
        if ($this->options) {
            return json_encode($model ? $model->getList() : array(), $this->options);
        } else {
            return json_encode($model ? $model->getList() : array());
        }
    }
}

?>