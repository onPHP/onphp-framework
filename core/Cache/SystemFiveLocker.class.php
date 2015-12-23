<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * System-V semaphores based locking.
 *
 * @ingroup Lockers
 **/
class SystemFiveLocker extends BaseLocker
{
    /**
     * @param $key
     * @return bool|null
     * @throws WrongArgumentException
     */
    public function get($key)
    {
        try {
            if (!isset($this->pool[$key])) {
                $this->pool[$key] = sem_get($key, 1, ONPHP_IPC_PERMS, false);
            }

            return sem_acquire($this->pool[$key]);
        } catch (BaseException $e) {
            return null;
        }

        Assert::isUnreachable();
    }

    /**
     * @param $key
     * @return bool|null
     */
    public function free($key)
    {
        if (isset($this->pool[$key])) {
            try {
                return sem_release($this->pool[$key]);
            } catch (BaseException $e) {
                // acquired by another process
                return false;
            }
        }

        return null;
    }

    /**
     * @param $key
     * @return bool|null
     */
    public function drop($key)
    {
        if (isset($this->pool[$key])) {
            try {
                return sem_remove($this->pool[$key]);
            } catch (BaseException $e) {
                unset($this->pool[$key]); // already race-removed
                return false;
            }
        }

        return null;
    }
}
