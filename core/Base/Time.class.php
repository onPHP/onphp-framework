<?php
/****************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

/**
 * Time's container and utilities.
 *
 * @ingroup Base
 **/
class Time implements Stringable
{
    /** @var int */
    private $hour = 0;

    /** @var int */
    private $minute = 0;

    /** @var int */
    private $second = 0;

    /** @var null|string */
    private $string = null;

    /**
     * Time constructor.
     *
     * @param $input
     * @throws WrongArgumentException
     */
    public function __construct($input)
    {
        if (Assert::checkInteger($input)) {
            $time = $input;
        } else {
            Assert::isString($input);
            $time = explode(':', $input);
        }

        $lenght = strlen($input);

        if (count($time) === 2) {
            $this
                ->setHour($time[0])
                ->setMinute($time[1]);
        } elseif (count($time) === 3) {
            $this
                ->setHour($time[0])
                ->setMinute($time[1])
                ->setSecond($time[2]);
        } else {
            switch ($lenght) {
                case 1:
                case 2:

                    $this->setHour(substr($input, 0, 2));
                    break;

                case 3:

                    $assumedHour = substr($input, 0, 2);

                    if ($assumedHour > 23) {
                        $this
                            ->setHour(substr($input, 0, 1))
                            ->setMinute(substr($input, 1, 2));
                    } else {
                        $this
                            ->setHour($assumedHour)
                            ->setMinute(substr($input, 2, 1) . '0');
                    }

                    break;

                case 4:
                case 5:
                case 6:

                    $this
                        ->setHour(substr($input, 0, 2))
                        ->setMinute(substr($input, 2, 2))
                        ->setSecond(substr($input, 4, 2));

                    break;

                default:
                    throw new WrongArgumentException('unknown format');
            }
        }
    }

    // currently supports '01:23:45', '012345', '1234', '12'

    /**
     * @return int
     */
    public function getHour() : int
    {
        return $this->hour;
    }

    /**
     * @param int $hour
     * @return Time
     * @throws WrongArgumentException
     */
    public function setHour(int $hour) : Time
    {

        Assert::isTrue(
            $hour >= 0 &&
            $hour <= 24,
            'wrong hour specified'
        );

        $this->hour = $hour;
        $this->string = null;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinute() : int
    {
        return $this->minute;
    }

    /**
     * @param int $minute
     * @return Time
     * @throws WrongArgumentException
     */
    public function setMinute(int $minute) : Time
    {
        Assert::isTrue(
            $minute >= 0
            && $minute <= 60,

            'wrong minute specified'
        );

        $this->minute = $minute;
        $this->string = null;

        return $this;
    }

    /**
     * @return int
     */
    public function getSecond() : int
    {
        return $this->second;
    }

    /**
     * @param int $second
     * @return Time
     * @throws WrongArgumentException
     */
    public function setSecond(int $second) : Time
    {
        $second = (int) $second;

        Assert::isTrue(
            $second >= 0
            && $second <= 60,

            'wrong second specified'
        );

        $this->second = $second;
        $this->string = null;

        return $this;
    }

    /// HH:MM
    /**
     * @param string $delimiter
     * @return string
     */
    public function toFullString(string  $delimiter = ':') : string
    {
        return
            $this->toString($delimiter)
            . $delimiter . (
            $this->second
                ? $this->doublize($this->second)
                : '00'
            );
    }

    /**
     * @param string $delimiter
     * @return null|string
     */
    public function toString($delimiter = ':') : string
    {
        if ($this->string === null) {
            $this->string =
                $this->doublize($this->hour)
                . $delimiter
                . $this->doublize($this->minute);
        }

        return (string)$this->string;
    }

    /**
     * @param $int
     * @return string
     */
    private function doublize(int $int) : string
    {
        return sprintf('%02d', $int);
    }

    /**
     * @return int
     */
    public function toMinutes() : int
    {
        return
            ($this->hour * 60)
            + $this->minute
            + round($this->second / 100, 0);
    }

    /**
     * @return int
     */
    public function toSeconds() : int
    {
        return
            ($this->hour * 3600)
            + ($this->minute * 60)
            + $this->second;
    }
}