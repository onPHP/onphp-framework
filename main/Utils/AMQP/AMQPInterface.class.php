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
 * AMQP stands for Advanced Message Queue Protocol, which is
 * an open standard middleware layer for message routing and queuing.
 **/
interface AMQPInterface
{
    /**
     * @return AMQPInterface
     **/
    public function connect();

    /**
     * @return AMQPInterface
     **/
    public function disconnect();

    /**
     * @return AMQPInterface
     **/
    public function reconnect();

    /**
     * @return boolean
     **/
    public function isConnected();

    /**
     * @return AMQPInterface
     **/
    public function getLink();


    /**
     * @param integer $id
     * @throws WrongArgumentException
     * @return AMQPChannelInterface
     **/
    public function createChannel($id);

    /**
     * @throws MissingElementException
     * @return AMQPChannelInterface
     **/
    public function getChannel($id);


    /**
     * @return array
     **/
    public function getChannelList();

    /**
     * @param integer $id
     * @throws MissingElementException
     * @return AMQPChannelInterface
     **/
    public function dropChannel($id);


    /**
     * @return AMQPCredentials
     */
    public function getCredentials();


    /**
     * @return bool
     */
    public function isAlive();


    /**
     * @param bool $alive
     * @return AMQPInterface
     */
    //public function setAlive($alive);
}

?>