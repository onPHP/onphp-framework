<?php
/***************************************************************************
 *   Copyright (C) 2004-2006 by Anton E. Lebedevich                           *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class MailException extends BaseException {};
	class MailNotSentException extends MailException {};
	
	class Mail
	{
		private $to				= null;
		private $text			= null;
		private $subject		= null;
		private $from			= null;
		private $siteEncoding	= null;
		private $mailEncoding	= null;
		
		public static function create()
		{
			return new self();
		}

		public function send()
		{
			if (empty($this->to))
				throw new WrongArgumentException("mail to: is not specified");

			$this->to = mb_convert_encoding($this->to, $this->mailEncoding);
			$this->from = mb_convert_encoding($this->from, $this->mailEncoding);

			$to = $this->to;

			$subject =
				 "=?".$this->mailEncoding."?B?"
				 .base64_encode(
				 	iconv(
				 		$this->siteEncoding,
				 		$this->mailEncoding
				 			.'//TRANSLIT',
				 		$this->subject
				 	)
				 )."?=";
				 
			$headers = '';
			if (!empty($this->from)) {
				$headers .= "From: ".$this->from."\n";
				$headers .= "Return-Path: ".$this->from."\n";
			}
			$headers .= "MIME-Version: 1.0\n";
			$headers .= "Content-type: text/html; charset=".$this->mailEncoding."\n";
			$headers .= "Content-Transfer-Encoding: 8bit\n";
			$headers .= "Date: ".date('r')."\n";
			
			$body = iconv(
				$this->siteEncoding,
				$this->mailEncoding.'//TRANSLIT', 
				$this->text
			);

			if (!mail($to, $subject, $body, $headers))
				throw new MailNotSentException();
		}
		
		public function setTo($to)
		{
			$this->to = $to;
			return $this;
		}

		public function setSubject($subject)
		{
			$this->subject = $subject;
			return $this;
		}
		
		public function setText($text)
		{
			$this->text = $text;
			return $this;
		}
		
		public function setFrom($from)
		{
			$this->from = $from;
			return $this;
		}
		
		public function setSiteEncoding($siteEncoding)
		{
			$this->siteEncoding = $siteEncoding;
			return $this;
		}
		
		public function setMailEncoding($mailEncoding)
		{
			$this->mailEncoding = $mailEncoding;
			return $this;
		}
	}
?>