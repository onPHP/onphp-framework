<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   Based on PEAR's Mail::MIME by Richard Heyes                           *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Mail
	**/
	final class MimeMail implements MailBuilder
	{
		private $parts = array();

		// should be built by build()
		private $body		= null;
		private $headers	= null;
		
		/**
		 * @return MimeMail
		**/
		public function addPart(MimePart $part)
		{
			$this->parts[] = $part;
			
			return $this;
		}
		
		public function build()
		{
			if (!$this->parts)
				throw new UnimplementedFeatureException();
			
			$boundary = '=_'.md5(microtime(true));
			
			$mail =
				MimePart::create()->
				setContentType('multipart/mixed')->
				setBoundary($boundary);
			
			$this->headers =
				"MIME-Version: 1.0\r\n"
				.$mail->getHeaders();

			foreach ($this->parts as $part)
				$this->body .=
					'--'.$boundary."\r\n"
					.$part->getHeaders()
					."\r\n\r\n"
					.$part->getEncodedBody()."\r\n";
			
			$this->body .= '--'.$boundary."--"."\r\n\r\n";
		}
		
		public function getEncodedBody()
		{
			Assert::isTrue(
				$this->body && $this->headers
			);
			
			return $this->body;
		}
		
		public function getHeaders()
		{
			Assert::isTrue(
				$this->body && $this->headers
			);
			
			return $this->headers;
		}
	}
?>