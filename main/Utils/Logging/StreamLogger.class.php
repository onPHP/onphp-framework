<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Utils
	**/
	final class StreamLogger extends BaseLogger
	{
		private $stream = null;
		
		public function __destruct()
		{
			try {
				$this->close();
			} catch (BaseException $e) {
				// boo.
			}
		}
		
		/**
		 * @return StreamLogger
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OutputStream
		**/
		public function getOutputStream()
		{
			return $this->stream;
		}
		
		/**
		 * @return StreamLogger
		**/
		public function setOutputStream(OutputStream $stream)
		{
			$this->stream = $stream;
			
			return $this;
		}
		
		/**
		 * @return StreamLogger
		**/
		public function flush()
		{
			if ($this->stream)
				$this->stream->flush();
			
			return $this;
		}
		
		/**
		 * @return StreamLogger
		**/
		public function close()
		{
			if ($this->stream) {
				
				$this->flush();
				$this->stream->close();
			
				$this->stream = null;
			}
			
			return $this;
		}
		
		/**
		 * @return StreamLogger
		**/
		protected function publish(LogRecord $record)
		{
			if (!$this->stream)
				return $this;
			
			$this->stream->write($record->toString()."\n");
			
			return $this;
		}
	}
?>