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
 * @see http://eaccelerator.net/
 *
 * @ingroup Lockers
 **/
class eAcceleratorLocker extends BaseLocker
{
    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return eaccelerator_lock($key);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function free($key)
    {
        return eaccelerator_unlock($key);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function drop($key)
    {
        return $this->free($key);
    }

    /**
     * @return bool
     */
    public function clean()
    {
        // will be cleaned out upon script's shutdown
        return true;
    }
}
