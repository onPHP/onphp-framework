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
abstract class AMQPDefaultConsumer implements AMQPConsumer
{
    /**
     * @var AMQPChannelInterface
     **/
    protected $channel = null;
    protected $consumerTag = null;
    protected $autoAcknowledge = false;
    protected $queueName = null;

    public function __construct(AMQPChannelInterface $channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return AMQPChannelInterface
     **/
    public function getChannel()
    {
        return $this->channel;
    }

    public function getConsumerTag()
    {
        return $this->consumerTag;
    }

    /**
     * @param $consumerTag
     * @return AMQPConsumer
     **/
    public function setConsumerTag($consumerTag)
    {
        $this->consumerTag = $consumerTag;

        return $this;
    }

    /**
     * @return void
     **/
    public function handleConsumeOk($consumerTag)
    {
        // no work to do
    }

    /**
     * @return void
     **/
    public function handleCancelOk($consumerTag)
    {
        // no work to do
    }

    /**
     * @return void
     **/
    public function handleDelivery(AMQPIncomingMessage $delivery)
    {
        // no work to do
    }

    /**
     * @return void
     **/
    public function handleChangeConsumerTag($fromTag, $toTag)
    {
        // no work to do
    }

    /**
     * @return string
     **/
    public function getQueueName()
    {
        return $this->queueName;
    }

    /**
     * @return AMQPDefaultConsumer
     **/
    public function setQueueName($name)
    {
        $this->queueName = $name;

        return $this;
    }

    public function isAutoAcknowledge()
    {
        return $this->autoAcknowledge;
    }

    /**
     * @return AMQPDefaultConsumer
     **/
    public function setAutoAcknowledge($boolean)
    {
        $this->autoAcknowledge = ($boolean === true);

        return $this;
    }

    /**
     * @return AMQPIncomingMessage
     **/
    public function getNextDelivery()
    {
        return $this->channel->getNextDelivery();
    }
}


