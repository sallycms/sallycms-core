<?php
/*
 * Copyright (c) 2012, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

/**
 * @ingroup util
 */
class sly_Util_JSON {
	protected static function getService() {
		return sly_Service_Factory::getService('File_JSON');
	}

	/**
	 * @param  string $filename
	 * @return string
	 */
	public static function getCacheFile($filename) {
		return self::getService()->getCacheFile($filename);
	}

	/**
	 * @param  string $filename
	 * @return boolean
	 */
	public static function hasChanges($filename) {
		return self::getService()->hasChanges($filename);
	}

	/**
	 * @param  string $origfile
	 * @param  string $cachefile
	 * @return boolean
	 */
	public static function isCacheValid($origfile, $cachefile) {
		return self::getService()->isCacheValid($origfile, $cachefile);
	}

	/**
	 * Cached loading of a JSON file
	 *
	 * @throws sly_Exception
	 * @param  string  $filename     Path to JSON file
	 * @param  boolean $forceCached  always return cached version (if it exists)
	 * @return mixed                 parsed content
	 */
	public static function load($filename, $forceCached = false) {
		return self::getService()->load($filename, $forceCached);
	}

	/**
	 * @param string $filename
	 * @param mixed  $data
	 */
	public static function dump($filename, $data) {
		self::getService()->dump($filename, $data);
	}
}
