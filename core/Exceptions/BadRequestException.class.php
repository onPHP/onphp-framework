<?php
/***************************************************************************
 *   Copyright (C) 2008 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Example:
	 * 
	 *	$form->import($request->getGet());
	 * 
	 *	if ($form->getErrors())
	 *		throw new BadRequestException('wrong arguments passed');
	 * 
	 * Use this exception instead of View('error')
	**/
	namespace Onphp;

	class BadRequestException extends BaseException {/* nop */}
?>