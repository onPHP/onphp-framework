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

/**
 * http://www.rabbitmq.com/amqp-0-9-1-reference.html#class.basic
 **/
abstract class AMQPBaseMessage
{
    const CONTENT_TYPE = 'content_type';
    const CONTENT_ENCODING = 'content_encoding';
    const MESSAGE_ID = 'message_id';
    const USER_ID = 'user_id';
    const APP_ID = 'app_id';
    const DELIVERY_MODE = 'delivery_mode';
    const PRIORITY = 'priority';
    const CORRELATION_ID = 'correlation_id';
    const TIMESTAMP = 'timestamp';
    const EXPIRATION = 'expiration';
    const TYPE = 'type';
    const REPLY_TO = 'reply_to';

    const DELIVERY_MODE_NONPERISISTENT = 1;
    const DELIVERY_MODE_PERISISTENT = 2;

    const PRIORITY_MIN = 0;
    const PRIORITY_MAX = 9;

    protected $properties = [];
    protected $timestamp = null;
    protected $body = null;

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return AMQPBaseMessage
     **/
    public function setProperties(array $assoc)
    {
        $this->properties = $assoc;

        if (isset($this->properties[self::TIMESTAMP])) {
            $this->timestamp =
                new Timestamp($this->properties[self::TIMESTAMP]);
        }

        return $this;
    }

    /**
     * @return AMQPBaseMessage
     **/
    public function setContentType($string)
    {
        $this->properties[self::CONTENT_TYPE] = $string;

        return $this;
    }

    public function getContentType()
    {
        return $this->getProperty(self::CONTENT_TYPE);
    }

    public function getProperty($key)
    {
        if (isset($this->properties[$key])) {
            return $this->properties[$key];
        }

        return null;
    }

    /**
     * @return AMQPBaseMessage
     **/
    public function setContentEncoding($string)
    {
        $this->properties[self::CONTENT_ENCODING] = $string;

        return $this;
    }

    public function getContentEncoding()
    {
        return $this->getProperty(self::CONTENT_ENCODING);
    }

    /**
     * @return AMQPBaseMessage
     **/
    public function setMessageId($string)
    {
        $this->properties[self::MESSAGE_ID] = $string;

        return $this;
    }

    public function getMessageId()
    {
        return $this->getProperty(self::MESSAGE_ID);
    }

    /**
     * @return AMQPBaseMessage
     **/
    public function setUserId($string)
    {
        $this->properties[self::USER_ID] = $string;

        return $this;
    }

    public function getUserId()
    {
        return $this->getProperty(self::USER_ID);
    }

    /**
     * @return AMQPBaseMessage
     **/
    public function setAppId($string)
    {
        $this->properties[self::APP_ID] = $string;

        return $this;
    }

    public function getAppId()
    {
        return $this->getProperty(self::APP_ID);
    }

    /**
     * Non-persistent (1) or persistent (2).
     *
     * @return AMQPBaseMessage
     **/
    public function setDeliveryMode($int)
    {
        Assert::isInteger($int, __METHOD__ . ": requires integer, given {$int}");

        Assert::isTrue(
            in_array(
                $int,
                [
                    self::DELIVERY_MODE_NONPERISISTENT,
                    self::DELIVERY_MODE_PERISISTENT
                ]
            ),
            __METHOD__ . ": unknown mode {$int}"
        );

        $this->properties[self::DELIVERY_MODE] = $int;

        return $this;
    }

    public function getDeliveryMode()
    {
        return $this->getProperty(self::DELIVERY_MODE);
    }

    /**
     * Message priority from 0 to 9.
     *
     * @return AMQPBaseMessage
     **/
    public function setPriority($int)
    {
        Assert::isInteger($int, __METHOD__);

        Assert::isTrue(
            ($int >= self::PRIORITY_MIN && $int <= self::PRIORITY_MAX),
            __METHOD__
        );

        $this->properties[self::PRIORITY] = $int;

        return $this;
    }

    public function getPriority()
    {
        return $this->getProperty(self::PRIORITY);
    }

    /**
     * @return AMQPBaseMessage
     **/
    public function setCorrelationId($string)
    {
        $this->properties[self::CORRELATION_ID] = $string;

        return $this;
    }

    public function getCorrelationId()
    {
        return $this->getProperty(self::CORRELATION_ID);
    }

    /**
     * @return Timestamp
     **/
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return AMQPBaseMessage
     **/
    public function setTimestamp(Timestamp $datetime)
    {
        $this->timestamp = $datetime;
        $this->properties[self::TIMESTAMP] = $datetime->toStamp();

        return $this;
    }

    /**
     * @return AMQPBaseMessage
     **/
    public function setExpiration($string)
    {
        $this->properties[self::EXPIRATION] = $string;

        return $this;
    }

    public function getExpiration()
    {
        return $this->getProperty(self::EXPIRATION);
    }

    /**
     * @return AMQPBaseMessage
     **/
    public function setType($string)
    {
        $this->properties[self::TYPE] = $string;

        return $this;
    }

    public function getType()
    {
        return $this->getProperty(self::TYPE);
    }

    /**
     * @return AMQPBaseMessage
     **/
    public function setReplyTo($string)
    {
        $this->properties[self::REPLY_TO] = $string;

        return $this;
    }

    public function getReplyTo()
    {
        return $this->getProperty(self::REPLY_TO);
    }
}

?>