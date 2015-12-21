<?php
/****************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

/**
 * @see IdentifiableTree
 *
 * @ingroup Helpers
 **/
abstract class NamedTree extends NamedObject
{
    private $parent = null;

    /**
     * @return NamedTree
     **/
    public function dropParent()
    {
        $this->parent = null;

        return $this;
    }

    /**
     * @return NamedTree
     **/
    public function getRoot()
    {
        $current = $this;
        $next = $this;

        while ($next) {
            $current = $next;
            $next = $next->getParent();
        }

        return $current;
    }

    /**
     * @return NamedTree
     **/
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return NamedTree
     **/
    public function setParent(NamedTree $parent)
    {
        Assert::brothers($this, $parent);

        $this->parent = $parent;

        return $this;
    }

    public function toString($delimiter = ' :: ')
    {
        $name = array($this->getName());

        $parent = $this;

        while ($parent = $parent->getParent())
            $name[] = $parent->getName();

        $name = array_reverse($name);

        return implode($delimiter, $name);
    }
}