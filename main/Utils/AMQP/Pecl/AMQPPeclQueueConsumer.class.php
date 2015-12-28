<?php

/***************************************************************************
 *   Copyright (C) 2012 by Evgeniya Tekalin                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
abstract class AMQPPeclQueueConsumer extends AMQPDefaultConsumer
{
    protected $cancel = false;
    protected $count = 0;
    protected $limit = 0;

    /**
     * @param int $limit
     * @return AMQPPeclQueueConsumer
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    public function handlePeclDelivery(AMQPEnvelope $delivery, AMQPQueue $queue = null)
    {
        $this->count++;

        if ($this->limit && $this->count >= $this->limit) {
            $this->setCancel(true);
        }

        return $this->handleDelivery(
            AMQPPeclIncomingMessageAdapter::convert($delivery)
        );
    }

    /**
     * @param type $cancel
     * @return AMQPPeclQueueConsumer
     */
    public function setCancel($cancel)
    {
        $this->cancel = ($cancel === true);
        return $this;
    }

    public function handleDelivery(AMQPIncomingMessage $delivery)
    {
        if ($this->cancel) {
            $this->handleCancelOk('');
            return false;
        }
    }
}

?>