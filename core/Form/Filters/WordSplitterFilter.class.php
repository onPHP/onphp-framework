<?php
/***************************************************************************
 *   Copyright (C) 2008 by Evgeniy N. Sokolov                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Filters
 **/
class WordSplitterFilter implements Filtrator
{
    /** @var int  */
    private $maxWordLength = 25;

    /** @var string  */
    private $delimer = '&#x200B;';


    /**
     * @param $value
     * @return mixed
     */
    public function apply($value)
    {
        return
            preg_replace(
                '/([^\s]{' . $this->getMaxWordLength() . ','
                . $this->getMaxWordLength() . '})([^\s])/u',
                '$1' . $this->getDelimer() . '$2',
                $value
            );
    }


    /**
     * @return int
     */
    public function getMaxWordLength() : int
    {
        return $this->maxWordLength;
    }

    /**
     * @param $length
     * @return WordSplitterFilter
     */
    public function setMaxWordLength($length)
    {
        $this->maxWordLength = $length;
        return $this;
    }

    /**
     * @return string
     */
    public function getDelimer() : string
    {
        return $this->delimer;
    }

    /**
     * @param $delimer
     * @return WordSplitterFilter
     */
    public function setDelimer($delimer)
    {
        $this->delimer = $delimer;
        return $this;
    }
}