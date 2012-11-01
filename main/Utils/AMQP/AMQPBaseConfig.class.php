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

	namespace Onphp;

	abstract class AMQPBaseConfig
	{
		protected $passive = false;
		protected $durable = false;
		protected $autodelete = false;
		protected $nowait = false;
		protected $arguments = array();

		public function getPassive()
		{
			return $this->passive;
		}

		public function setPassive($passive)
		{
			$this->passive = $passive === true;

			return $this;
		}

		public function getDurable()
		{
			return $this->durable;
		}

		public function setDurable($durable)
		{
			$this->durable = $durable === true;

			return $this;
		}

		public function getAutodelete()
		{
			return $this->autodelete;
		}

		public function setAutodelete($autodelete)
		{
			$this->autodelete = $autodelete === true;

			return $this;
		}

		public function getNowait()
		{
			return $this->nowait;
		}

		public function setNowait($nowait)
		{
			$this->nowait = $nowait === true;

			return $this;
		}

		public function setArguments(array $assoc)
		{
			$this->arguments = $assoc;

			return $this;
		}

		/**
		 * @param \Onphp\AMQPBitmaskResolver $resolver
		 * @return integer - it's bitmask
		**/
		public function getBitmask(AMQPBitmaskResolver $resolver)
		{
			return $resolver->getBitmask($this);
		}

		public function getArguments()
		{
			return $this->arguments;
		}
	}
?>