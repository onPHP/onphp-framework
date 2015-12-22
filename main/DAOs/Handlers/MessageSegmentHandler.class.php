<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup DAOs
 **/
final class MessageSegmentHandler implements SegmentHandler
{
    private $id = null;

    public function __construct($segmentId)
    {
        $this->id = $segmentId;
    }

    public function touch($key)
    {
        try {
            $q = msg_get_queue($this->id, ONPHP_IPC_PERMS);
        } catch (BaseException $e) {
            // race
            return false;
        }

        try {
            return msg_send($q, $key, 1, false, false);
        } catch (BaseException $e) {
            // queue is full, rotate it.
            return msg_remove_queue($q);
        }

        Assert::isUnreachable();
    }

    public function unlink($key)
    {
        try {
            $q = msg_get_queue($this->id, ONPHP_IPC_PERMS);
        } catch (BaseException $e) {
            // race
            return false;
        }

        $type = $msg = null;

        return msg_receive($q, $key, $type, 2, $msg, false, MSG_IPC_NOWAIT);
    }

    public function ping($key)
    {
        try {
            $q = msg_get_queue($this->id, ONPHP_IPC_PERMS);
        } catch (BaseException $e) {
            // race
            return false;
        }

        $type = $msg = null;

        // YANETUT
        if (msg_receive($q, $key, $type, 2, $msg, false, MSG_IPC_NOWAIT)) {
            try {
                msg_send($q, $key, 1, false, false);
            } catch (BaseException $e) {/* lost key due to race */
            }

            return true;
        }

        return false;
    }

    public function drop()
    {
        try {
            $q = msg_get_queue($this->id, ONPHP_IPC_PERMS);
        } catch (BaseException $e) {
            // removed in race
            return true;
        }

        if (!msg_remove_queue($q)) {
            // trying to flush manually
            $type = $msg = null;

            while (msg_receive($q, 0, $type, 2, $msg, false, MSG_IPC_NOWAIT)) {
                // do nothing
            }
        }

        return true;
    }
}