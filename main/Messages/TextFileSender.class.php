<?php

/***************************************************************************
 *   Copyright (C) 2009 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
final class TextFileSender implements MessageQueueSender
{
    private $queue = null;
    private $stream = null;

    public static function create()
    {
        return new self;
    }

    /**
     * @return MessageQueue
     **/
    public function getQueue()
    {
        return $this->queue;
    }

    public function setQueue(MessageQueue $queue)
    {
        Assert::isInstance($queue, 'TextFileQueue');

        $this->queue = $queue;

        return $this;
    }

    public function send(Message $message)
    {
        if (!$this->queue) {
            throw new WrongStateException('you must set the queue first');
        }

        Assert::isInstance($message, 'TextMessage');

        $this->getStream()->write(
            $message->getTimestamp()->toString() . "\t"
            . str_replace(PHP_EOL, ' ', $message->getText()) . PHP_EOL
        );
    }

    private function getStream()
    {
        if (!$this->stream) {
            Assert::isNotNull($this->queue->getFileName());

            $this->stream = (new FileOutputStream($this->queue->getFileName(), true));
        }

        return $this->stream;
    }
}

?>