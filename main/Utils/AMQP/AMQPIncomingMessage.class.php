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
class AMQPIncomingMessage extends AMQPBaseMessage
{
    const COUNT = 'count';
    const ROUTING_KEY = 'routing_key';
    const DELIVERY_TAG = 'delivery_tag';
    const EXCHANGE = 'exchange';
    const BODY = 'msg';
    const CONSUME_BODY = 'message_body';
    const CONSUMER_TAG = 'consumer_tag';
    const REDELIVERED = 'redelivered';
    protected static $mandatoryFields = [
        self::ROUTING_KEY, self::DELIVERY_TAG, self::EXCHANGE
    ];
    protected $count = 0;
    protected $routingKey = null;
    protected $exchange = null;
    protected $deliveryTag = null;
    protected $redelivered = null;
    protected $consumerTag = null;

    /**
     * @return AMQPIncomingMessage
     **/
    public static function spawn(array $assoc)
    {
        return (new self())->fill($assoc);
    }

    /**
     * @return AMQPIncomingMessage
     **/
    protected function fill(array $assoc)
    {
        $this->checkMandatory($assoc);

        if (isset($assoc[self::COUNT])) {
            $this->setCount($assoc[self::COUNT]);
            unset($assoc[self::COUNT]);
        }

        $this->setRoutingKey($assoc[self::ROUTING_KEY]);
        $this->setDeliveryTag($assoc[self::DELIVERY_TAG]);
        $this->setExchange($assoc[self::EXCHANGE]);

        if (isset($assoc[self::BODY])) {
            $this->setBody($assoc[self::BODY]);
            unset($assoc[self::BODY]);
        }

        if (isset($assoc[self::CONSUME_BODY])) {
            $this->setBody($assoc[self::CONSUME_BODY]);
            unset($assoc[self::CONSUME_BODY]);
        }

        if (isset($assoc[self::CONSUMER_TAG])) {
            $this->setConsumerTag($assoc[self::CONSUMER_TAG]);
            unset($assoc[self::CONSUMER_TAG]);
        }

        if (isset($assoc[self::REDELIVERED])) {
            $this->setRedelivered($assoc[self::REDELIVERED]);
            unset($assoc[self::REDELIVERED]);
        }

        //unset mandatory
        unset(
            $assoc[self::ROUTING_KEY],
            $assoc[self::DELIVERY_TAG],
            $assoc[self::EXCHANGE]
        );

        $this->setProperties($assoc);

        return $this;
    }

    protected function checkMandatory(array $assoc)
    {
        foreach (self::$mandatoryFields as $field) {
            Assert::isIndexExists(
                $assoc, $field, "Mandatory field '{$field}' not found"
            );
        }

        return $this;
    }

    /**
     * @return AMQPIncomingMessage
     **/
    public static function create()
    {
        return new self;
    }

    public function getRedelivered()
    {
        return $this->redelivered;
    }

    /**
     * @return AMQPIncomingMessage
     **/
    public function setRedelivered($redelivered)
    {
        $this->redelivered = $redelivered;

        return $this;
    }

    public function getConsumerTag()
    {
        return $this->consumerTag;
    }

    /**
     * @return AMQPIncomingMessage
     **/
    public function setConsumerTag($consumerTag)
    {
        $this->consumerTag = $consumerTag;

        return $this;
    }

    public function getCount()
    {
        return $this->count;
    }

    /**
     * @return AMQPIncomingMessage
     **/
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    public function getRoutingKey()
    {
        return $this->routingKey;
    }

    /**
     * @return AMQPIncomingMessage
     **/
    public function setRoutingKey($routingKey)
    {
        $this->routingKey = $routingKey;

        return $this;
    }

    public function getExchange()
    {
        return $this->exchange;
    }

    /**
     * @return AMQPIncomingMessage
     **/
    public function setExchange($exchange)
    {
        $this->exchange = $exchange;

        return $this;
    }

    public function getDeliveryTag()
    {
        return $this->deliveryTag;
    }

    /**
     * @return AMQPIncomingMessage
     **/
    public function setDeliveryTag($deliveryTag)
    {
        $this->deliveryTag = $deliveryTag;

        return $this;
    }

    public function isEmptyQueue()
    {
        return $this->count == -1;
    }
}

