<?php
/*
 * Copyright (c) 2014, webvariants GmbH & Co. KG, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

/**
 * @ingroup util
 * @deprecated  Do not use this class, it adds an incredible overhead to simply reading the
 *              file and doing a json_decode() on its content.
 */
class sly_Service_File_JSON extends sly_Service_File_Base {
	/**
	 * @throws sly_Exception
	 * @return string
	 */
	protected function getCacheDir() {
		$dir = SLY_TEMPFOLDER.'/sally/json-cache';
		return sly_Util_Directory::create($dir, null, true);
	}

	/**
	 * @param  string $filename
	 * @return mixed
	 */
	protected function readFile($filename) {
		$contents = trim(file_get_contents($filename));
		if (mb_strlen($contents) === 0) return null;
		return json_decode($contents, true);
	}

	/**
	 * @param  string $filename
	 * @param  mixed  $data
	 * @return int               number of written bytes
	 */
	protected function writeFile($filename, $data) {
		// JSON formatting is available since PHP 5.4. Since we prettyprint
		// YAML, we should do it here as well, if possible.
		// 448 is (JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).
		return file_put_contents($filename, json_encode($data, 448), LOCK_EX);
	}
}
