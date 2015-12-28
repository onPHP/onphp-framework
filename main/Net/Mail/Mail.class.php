<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Mail
 *
 * Note: when relay rejects your mail, try to
 * setSendmailAdditionalArgs('-f from.addr@example.com').
 * See 'man sendmail' for details.
 **/
final class Mail
{
    private $to = null;
    private $cc = null;
    private $text = null;
    private $subject = null;
    private $from = null;
    private $encoding = null;
    private $contentType = null;
    private $returnPath = null;
    private $headers = null;

    private $sendmailAdditionalArgs = null;

    /**
     * @return Mail
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @return Mail
     **/
    public function send()
    {
        if ($this->to == null) {
            throw new WrongArgumentException("mail to: is not specified");
        }

        $siteEncoding = mb_get_info('internal_encoding');

        if (!$this->encoding
            || $this->encoding == $siteEncoding
        ) {
            $encoding = $siteEncoding;
            $to = $this->to;
            $from = $this->from;
            $subject =
                "=?" . $encoding . "?B?"
                . base64_encode($this->subject)
                . "?=";
            $body = $this->text;
            $returnPath = $this->returnPath;
        } else {
            $encoding = $this->encoding;
            $to = mb_convert_encoding($this->to, $encoding);

            if ($this->from) {
                $from = mb_convert_encoding($this->from, $encoding);
            } else {
                $from = null;
            }

            if ($this->returnPath) {
                $returnPath = mb_convert_encoding($this->returnPath, $encoding);
            } else {
                $returnPath = null;
            }

            $subject =
                "=?" . $encoding . "?B?"
                . base64_encode(
                    iconv(
                        $siteEncoding,
                        $encoding . '//TRANSLIT',
                        $this->subject
                    )
                ) . "?=";

            $body = iconv(
                $siteEncoding,
                $encoding . '//TRANSLIT',
                $this->text
            );
        }

        $headers = null;

        $returnPathAtom =
            $returnPath !== null
                ? $returnPath
                : $from;

        if ($from != null) {
            $headers .= "From: " . $from . "\n";
            $headers .= "Return-Path: " . $returnPathAtom . "\n";
        }

        if ($this->cc != null) {
            $headers .= "Cc: " . $this->cc . "\n";
        }

        if (!$this->getHeaders()) {
            if ($this->contentType === null) {
                $this->contentType = 'text/plain';
            }

            $headers .=
                "Content-type: " . $this->contentType
                . "; charset=" . $encoding . "\n";

            $headers .= "Content-Transfer-Encoding: 8bit\n";
            $headers .= "Date: " . date('r') . "\n";
        } else {
            $headers .= $this->getHeaders();
        }

        if (
        !mail(
            $to, $subject, $body, $headers,
            $this->getSendmailAdditionalArgs()
        )
        ) {
            throw new MailNotSentException();
        }

        return $this;
    }

    /**
     * @return null
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    public function getSendmailAdditionalArgs()
    {
        return $this->sendmailAdditionalArgs;
    }

    /**
     * @return Mail
     **/
    public function setSendmailAdditionalArgs($sendmailAdditionalArgs)
    {
        $this->sendmailAdditionalArgs = $sendmailAdditionalArgs;
        return $this;
    }

    /**
     * @return Mail
     **/
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return Mail
     **/
    public function setCc($cc)
    {
        $this->cc = $cc;
        return $this;
    }

    /**
     * @return Mail
     **/
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return Mail
     **/
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return Mail
     **/
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return Mail
     **/
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
        return $this;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @return Mail
     **/
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    public function getReturnPath()
    {
        return $this->returnPath;
    }

    /**
     * @return Mail
     **/
    public function setReturnPath($returnPath)
    {
        $this->returnPath = $returnPath;
        return $this;
    }
}

?>
