<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * File uploads helper.
 *
 * @ingroup Primitives
 **/
class PrimitiveArrayOfFiles extends PrimitiveFile
{

	public function import($scope)
	{
		$documents = [];
		if (isset($scope[$this->name]) && is_array($scope[$this->name])) {
			foreach($scope[$this->name] as $field => $info) {
				foreach ($info as $index => $value) {
					$documents[$index][$field] = $value;
				}
			}
			$scope[$this->name] = $documents;
		} else {
			return null;
		}

		foreach ($scope[$this->name] as $tempFile) {

			if ($tempFile['error'] && $tempFile['error'] == UPLOAD_ERR_NO_FILE) {
				return null;
			}

			if (isset($tempFile['tmp_name'])) {
				$file = $tempFile['tmp_name'];
			} else {
				return false;
			}

			if (is_readable($file) && $this->checkUploaded($file)) {
				$size = filesize($file);
			} else {
				return false;
			}

			if (class_exists('finfo')) {
				$finfo = new finfo(FILEINFO_MIME_TYPE);
				$this->mimeType = $finfo->file($file);
			} else {
				$this->mimeType = $tempFile['type'];
			}

			if (!$this->isAllowedMimeType()) {
				return false;
			}

			if ( ($this->max && ($size > $this->max))
				&& ($this->min && ($size < $this->min))
			) {
				return false;
			}

		}

		$this->raw = $scope;
		$this->value = $documents;
		$this->imported = true;
		return true;
	}

}
?>