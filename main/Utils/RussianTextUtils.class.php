<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Sveta A. Smirnova                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Utils
 **/
final class RussianTextUtils extends StaticFactory
{
    const MALE = 0;
    const FEMALE = 1;
    const NEUTRAL = 2;

    private static $orderedSuffixes = [
        self::MALE => ['ый', 'ой', 'ий'],
        self::FEMALE => ['ая', 'ья', null],
        self::NEUTRAL => ['ое', 'ье', null]
    ];

    private static $orderedDigits = [
        'перв',
        'втор',
        'трет',
        'четвёрт',
        'пят',
        'шест',
        'седьм',
        'восьм',
        'девят',
        'десят'
    ];

    private static $bytePrefixes = [
        null, 'К', 'М', 'Г', 'Т', 'П'
    ];

    private static $lettersMapping = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g',
        'д' => 'd', 'е' => 'e', 'ё' => 'jo', 'ж' => 'zh',
        'з' => 'z', 'и' => 'i', 'й' => 'jj', 'к' => 'k',
        'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
        'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'c',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shh', 'ъ' => '\'',
        'ы' => 'y', 'ь' => '\'', 'э' => 'eh', 'ю' => 'ju',
        'я' => 'ja',

        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G',
        'Д' => 'D', 'Е' => 'E', 'Ё' => 'JO', 'Ж' => 'ZH',
        'З' => 'Z', 'И' => 'I', 'Й' => 'JJ', 'К' => 'K',
        'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
        'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
        'У' => 'U', 'Ф' => 'F', 'Х' => 'KH', 'Ц' => 'C',
        'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SHH', 'Ъ' => '\'',
        'Ы' => 'Y', 'Ь' => '\'', 'Э' => 'EH', 'Ю' => 'JU',
        'Я' => 'JA'
    ];

    private static $monthInGenitiveCase = [
        'января', 'февраля', 'марта', 'апреля',
        'мая', 'июня', 'июля', 'августа', 'сентября',
        'октября', 'ноября', 'декабря'
    ];

    private static $flippedLettersMapping = [];

    private static $ambiguousDetection = false;

    public static function getMonthByGenitiveCase($string)
    {
        static $flipped = null;

        if (!$flipped) {
            $flipped = array_flip(self::$monthInGenitiveCase);
        }

        if (isset($flipped[$string])) {
            return $flipped[$string] + 1;
        }

        throw new MissingElementException();
    }

    public static function getMonthInSubjectiveCase($month)
    {
        static $months = [
            'январь', 'февраль', 'март', 'апрель',
            'май', 'июнь', 'июль', 'август', 'сентябрь',
            'октябрь', 'ноябрь', 'декабрь'
        ];

        return $months[$month - 1];
    }

    public static function getDayOfWeek($day, $full = false)
    {
        static $weekDays = [
            'вс', 'пн', 'вт', 'ср',
            'чт', 'пт', 'сб', 'вс'
        ];

        static $weekDaysFull = [
            'Воскресенье', 'Понедельник', 'Вторник', 'Среда',
            'Четверг', 'Пятница', 'Суббота', 'Воскресенье'
        ];

        if ($full) {
            return $weekDaysFull[$day];
        } else {
            return $weekDays[$day];
        }
    }

    public static function getDateAsText(Timestamp $date, $todayWordNeed = true)
    {
        $dayStart = Timestamp::makeToday();
        $tomorrowDayStart = $dayStart->spawn('+1 day');

        if (
            (Timestamp::compare($date, $dayStart) == 1)
            && (Timestamp::compare($date, $tomorrowDayStart) == -1)
        ) {
            return
                (
                $todayWordNeed === true
                    ? 'сегодня '
                    : null
                )
                . 'в '
                . date('G:i', $date->toStamp());
        }

        $yesterdayStart = $dayStart->spawn('-1 day');

        if (
            (Timestamp::compare($date, $yesterdayStart) == 1)
            && (Timestamp::compare($date, $dayStart) == -1)
        ) {
            return 'вчера в ' . date('G:i', $date->toStamp());
        }

        return date('j.m.Y в G:i', $date->toStamp());
    }

    public static function friendlyFileSize($size, $precision = 2)
    {
        if ($size < 1024) {
            return
                $size . ' ' . self::selectCaseForNumber(
                    $size, ['байт', 'байта', 'байт']
                );
        } else {
            return TextUtils::friendlyFileSize(
                $size, $precision, self::$bytePrefixes, true
            ) . 'Б';
        }
    }

    /**
     * Selects russian case for number.
     * for example:
     *    1 результат
     *    2 результата
     *    5 результатов
     * @param $number integer
     * @param $cases words to select from array('результат', 'результата', 'результатов')
     **/
    public static function selectCaseForNumber($number, $cases)
    {
        if (($number % 10) == 1 && ($number % 100) != 11) {

            return $cases[0];

        } elseif (
            ($number % 10) > 1
            && ($number % 10) < 5
            && ($number % 100 < 10 || $number % 100 > 20)
        ) {

            return $cases[1];

        } else {
            return $cases[2];
        }
    }

    public static function getHumanDay(Date $date, $wordDayNeed = true)
    {
        $today = Date::makeToday();
        $tomorrow = $today->spawn('+1 day');

        if ($date->toDate() == $today->toDate() && $wordDayNeed == true) {
            return 'сегодня';
        } elseif ($date->toDate() == $tomorrow->toDate() && $wordDayNeed == true) {
            return 'завтра';
        } else {
            return
                (int) $date->getDay()
                . ' '
                . RussianTextUtils::getMonthInGenitiveCase(
                    $date->getMonth()
                );
        }
    }

    /**
     * doesn't duplicate strftime('%B', ...)
     * only when 'russian' locale set in windoze
     **/
    public static function getMonthInGenitiveCase($month)
    {
        return self::$monthInGenitiveCase[$month - 1];
    }

    public static function toTranslit($sourceString)
    {
        return strtr($sourceString, self::$lettersMapping);
    }

    public static function toRussian($sourceString)
    {
        if (!self::$flippedLettersMapping) {
            self::$flippedLettersMapping =
                array_flip(self::$lettersMapping);
        }

        return strtr($sourceString, self::$flippedLettersMapping);
    }

    /**
     * based on CPAN's Lingua::DetectCharset.
     * Thanks to John Neystadt, http://www.neystadt.org/john/
     **/
    public static function detectEncoding($data)
    {
        static $tables = [
            'KOI8-R' => [], 'WINDOWS-1251' => []
        ];

        $table = CyrillicPairs::getTable();

        $score = ['UTF-8' => 0, 'KOI8-R' => 0, 'WINDOWS-1251' => 0];

        foreach (
            preg_split('~[\.\,\-\s\:\;\?\!\'\"\(\)\d<>]~', $data) as $word
        ) {
            for ($i = 0; $i < strlen($word) - 2; ++$i) {
                foreach (array_keys($score) as $encoding) {
                    if ($encoding == 'UTF-8') {
                        $pairLengthBytes = 4;
                    } else {
                        $pairLengthBytes = 2;
                    }

                    if ($i + $pairLengthBytes >= strlen($word)) {
                        continue;
                    }

                    $pair = substr($word, $i, $pairLengthBytes);

                    $value = 0;

                    if ($encoding === 'UTF-8') {

                        if (isset($table[$pair])) {
                            $value = $table[$pair];
                        }

                    } elseif (
                    isset($tables[$encoding][$pair])
                    ) {
                        $value = $tables[$encoding][$pair];

                    } else {

                        $utf8Pair = mb_convert_encoding(
                            $pair, 'UTF-8', $encoding
                        );

                        if (isset($table[$utf8Pair])) {
                            $value = $table[$utf8Pair];
                            $tables[$encoding][$pair] = $table[$utf8Pair];
                        } else {
                            $tables[$encoding][$pair] = false;
                        }
                    }

                    $score[$encoding] += $value;
                }

            }
        }

        $koi8Ratio =
            $score['KOI8-R']
            / ($score['WINDOWS-1251'] + $score['UTF-8'] + 1);

        $winRatio =
            $score['WINDOWS-1251']
            / ($score['KOI8-R'] + $score['UTF-8'] + 1);

        $utf8Ratio =
            $score['UTF-8']
            / ($score['KOI8-R'] + $score['WINDOWS-1251'] + 1);

        $minRatio = 1.5;
        $doubtRatio = 1;

        if (
            ($koi8Ratio < $minRatio && $koi8Ratio > $doubtRatio)
            || ($winRatio < $minRatio && $winRatio > $doubtRatio)
            || ($utf8Ratio < $minRatio && $utf8Ratio > $doubtRatio)
        ) {
            self::$ambiguousDetection = true;
        } else {
            self::$ambiguousDetection = false;
        }

        if ($koi8Ratio > $winRatio && $koi8Ratio > $utf8Ratio) {
            return 'KOI8-R';
        }

        if ($winRatio > $utf8Ratio) {
            return 'WINDOWS-1251';
        }

        if ($winRatio + $koi8Ratio + $utf8Ratio > 0) {
            return 'UTF-8';
        }

        return 'ASCII';
    }

    public static function isAmbiguousDetection()
    {
        return self::$ambiguousDetection;
    }
}

