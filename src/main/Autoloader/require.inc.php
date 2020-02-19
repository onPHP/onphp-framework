<?php
/***************************************************************************
 *   Copyright (C) 2012 by Aleksey S. Denisov                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

//interfaces:
require 'Autoloader.class.php';
require 'AutoloaderWithNamespace.class.php';
require 'AutoloaderRecachable.class.php';
//classes:
require 'AutoloaderClassNotFound.class.php';
require 'AutoloaderClassPathCache.class.php';
require 'AutoloaderNoCache.class.php';
require 'AutoloaderPool.class.php';
require dirname(dirname(__DIR__)).'/core/Exceptions/BaseException.class.php';
require dirname(dirname(__DIR__)).'/core/Exceptions/ClassNotFoundException.class.php';
require 'NamespaceDirScaner.class.php';
require 'NamespaceDirScanerOnPHP.class.php';
require 'NamespaceDirScanerPSR0.class.php';
require 'NamespaceResolver.class.php';
require 'NamespaceResolverOnPHP.class.php';
require 'NamespaceResolverPSR0.class.php';

?>