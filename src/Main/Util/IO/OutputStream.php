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

namespace OnPHP\Main\Util\IO;

/**
 * @ingroup Utils
**/
abstract class OutputStream
{
	/**
	 * MUST send either whole buffer or nothing at all
	 * or throw IOException
	 * 
	 * NOTE: if buffer is too large to send it at once and first chunk of
	 * data has been sent successfully, it MUST BLOCK until all data is
	 * sent, or throw IOException. In this case it MUST NOT throw
	 * IOTimedOutException due to impossibility of detecting what data
	 * has been already sent.
	 * 
	 * It is abnormal state. Maybe you should use some kind of
	 * non-blocking channels instead?
	**/
	abstract public function write($buffer);

	/**
	 * @return OutputStream
	**/
	public function flush()
	{
		/* nop */

		return $this;
	}

	/**
	 * @return OutputStream
	**/
	public function close()
	{
		/* nop */

		return $this;
	}
}
?>