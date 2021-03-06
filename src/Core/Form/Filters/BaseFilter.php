<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Core\Form\Filters;

use OnPHP\Core\Base\Singleton;
use OnPHP\Core\Base\Instantiatable;

/**
 * Filter's template.
 * 
 * @ingroup Filters
 * @ingroup Module
**/
abstract class BaseFilter
	extends Singleton
	implements Filtrator, Instantiatable {/*_*/}
?>