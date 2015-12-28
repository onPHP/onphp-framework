<?php

/***************************************************************************
 *   Copyright (C) 2011 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
final class AMQPOutgoingMessage extends AMQPBaseMessage
{
    protected $mandatory = false;
    protected $immediate = false;

    /**
     * @deprecated
     * @return AMQPOutgoingMessage
     **/
    public static function create()
    {
        return new self;
    }

    public function getBitmask(AMQPBitmaskResolver $config)
    {
        return $config->getBitmask($this);
    }

    public function getMandatory()
    {
        return $this->mandatory;
    }

    /**
     * @return AMQPOutgoingMessage
     **/
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;

        return $this;
    }

    public function getImmediate()
    {
        return $this->immediate;
    }

    /**
     * @return AMQPOutgoingMessage
     **/
    public function setImmediate($immediate)
    {
        $this->immediate = $immediate;

        return $this;
    }
}

?>