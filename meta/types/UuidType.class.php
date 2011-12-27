<?php
/***************************************************************************
 *	 Created by Alexey V. Gorbylev at 27.12.2011                           *
 *	 email: alex@gorbylev.ru, icq: 1079586                                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

final class UuidType extends BasePropertyType {

	public function getPrimitiveName() {
		return 'uuid';
	}

	public function getDeclaration() {
		return null;
	}

	public function toColumnType() {
		return 'DataType::create(DataType::UUID)';
	}

	public function isMeasurable() {
		return false;
	}

}
