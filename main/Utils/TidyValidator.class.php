<?php
/***************************************************************************
 *   Copyright (C) 2007 by Sergey M. Skachkov                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Small Tidy-based HTML validator.
 *
 * @ingroup Utils
 **/
final class TidyValidator
{
    private $content = null;
    private $messages = null;
    private $errorCount = null;
    private $warningCount = null;

    private $config = [
        'output-xhtml' => true,
        'doctype' => 'strict',
        'wrap' => 0,
        'quote-marks' => true,
        'drop-empty-paras' => true
    ];

    private $header = '
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <title></title>
            </head>
            <body>';

    private $headerLines = 7;

    private $encoding = 'utf8';

    /**
     * @deprecated
     *
     * @return TidyValidator
     **/
    public static function create()
    {
        return new self;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function getErrorCount()
    {
        return $this->errorCount;
    }

    public function getWarningCount()
    {
        return $this->warningCount;
    }

    /**
     * Do the content validation and repair it.
     *
     * For example:
     *    $repairedContent =
     *        (new TidyValidator())->
     *        setContent('<b>blablabla')->
     *        validateContent()->
     *        getContent();
     *
     * Or just:
     *    $repairedContent =
     *        (new TidyValidator())->
     *        validateContent('<b>blablabla')->
     *        getContent();
     *
     * @param $content content to validate
     * @return TidyValidator
     **/
    public function validateContent($content = null)
    {
        static $symbols = [
            '…' => '&hellip;',
            '™' => '&trade;',
            '©' => '&copy;',
            '№' => '&#8470;',
            '—' => '&mdash;',
            '–' => '&mdash;',
            '«' => '&laquo;',
            '»' => '&raquo;',
            '„' => '&bdquo;',
            '“' => '&ldquo;',
            '•' => '&bull;',
            '®' => '&reg;',
            '¼' => '&frac14;',
            '½' => '&frac12;',
            '¾' => '&frac34;',
            '±' => '&plusmn;'
        ];

        if ($content) {
            $this->setContent($content);
        } elseif (!$this->getContent()) {
            return $this;
        }

        $tidy = tidy_parse_string(
            $this->getHeader() . "\n" . $this->getContent() . "\n</body></html>",
            $this->getConfig(),
            $this->getEncoding()
        );

        $this->errorCount = tidy_error_count($tidy);
        $this->warningCount = tidy_warning_count($tidy);

        $rawMessages = tidy_get_error_buffer($tidy);
        $out = null;

        if (!empty($rawMessages)) {
            $errorStrings =
                explode(
                    "\n",
                    htmlspecialchars($rawMessages)
                );

            foreach ($errorStrings as $string) {
                list (/* $line */, $num, /* $col */, $rest) =
                    explode(' ', $string, 4);

                $out .=
                    (
                    $out == null
                        ? null
                        : "\n"
                    )
                    . 'line '
                    . ($num - ($this->headerLines))
                    . ' column ' . $rest;
            }
        }

        $tidy->cleanRepair();

        $outContent = [];

        preg_match_all('/<body>(.*)<\/body>/s', $tidy, $outContent);

        Assert::isTrue(isset($outContent[1][0]));

        $outContent[1][0] = strtr($outContent[1][0], $symbols);

        $crcBefore = crc32(
            preg_replace('/[\t\n\r\0 ]/', null, $this->getContent())
        );
        $crcAfter = crc32(
            preg_replace('/[\t\n\r\0 ]/', null, $outContent[1][0])
        );

        if ($crcBefore != $crcAfter) {
            if (
                (
                    $this->countTags('<[\t ]*p[\t ]*>', $this->getContent())
                    != $this->countTags('<[\t ]*p[\t ]*>', $outContent[1][0])
                ) || (
                    $this->countTags(
                        '<[\t ]*\/[\t ]*p[\t ]*>',
                        $this->getContent()
                    )
                    != $this->countTags(
                        '<[\t ]*\/[\t ]*p[\t ]*>',
                        $outContent[1][0]
                    )
                )
            ) {
                $out =
                    (
                    $out == null
                        ? null
                        : $out . "\n\n"
                    )
                    . 'Paragraphs have been changed, please review content';
            } else {
                if (!$out) {
                    $out = 'Content has been changed, please review';
                }
            }
        }

        $this->messages = $out;
        $this->content = $outContent[1][0];

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets content to validate.
     *
     * For example: (new TidyValidator())->setContent('<b>blabla</b>');
     *
     * @param $content content itself
     * @return TidyValidator
     **/
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Sets header for content. There is default header (see code).
     *
     * @param $header header string
     * @return TidyValidator
     **/
    public function setHeader($header)
    {
        $this->header = $header;
        $this->headerLines = count(explode("\n", $header));

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Sets configuration array for tidy. There is default config (see code).
     *
     * For example: (new TidyValidator())->setConfig('output-xhtml' => true);
     *
     * @param $config array with tidy's configuration
     * @return TidyValidator
     **/
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Sets encoding for content. There is default encoding 'utf8'.
     *
     * For example: (new TidyValidator())->setEncoding('utf8');
     *
     * @param $encoding encoding name
     * @return TidyValidator
     **/
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    private function countTags($tag, $text)
    {
        $matches = [];

        if (preg_match_all("/$tag/i", $text, $matches)) {
            return count($matches[0]);
        }

        return 0;
    }
}

