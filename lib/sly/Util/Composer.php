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
class sly_Util_Composer {
	protected $filename;
	protected $mtime;
	protected $data;

	const EXTRA_SUBKEY = 'sallycms';

	public function __construct($filename) {
		if (!is_file($filename)) {
			throw new sly_Exception(t('file_not_found', $filename));
		}

		$this->filename = realpath($filename);
		$this->mtime    = null;
		$this->data     = false;
	}

	public function getFilename() {
		return $this->filename;
	}

	public function getContent() {
		if ($this->data === false) {
			$this->data  = sly_Util_JSON::load($this->filename, false, true);
			$this->mtime = filemtime($this->filename);
		}

		return $this->data;
	}

	public function revalidate() {
		if ($this->mtime !== null) {
			if (!file_exists($this->filename)) {
				$this->data  = null;
				$this->mtime = time();
				return true;
			}

			if (filemtime($this->filename) > $this->mtime) {
				$this->data = false;
				$this->getContent();
				return true;
			}
		}

		return false;
	}

	/**
	 * @param  string  $key
	 * @param  boolean $tryExtra  if true $key is first searched in /extra/sallycms/$key, before /$key ist searched
	 * @return string
	 */
	public function getKey($key, $tryExtra = true) {
		if ($tryExtra) {
			$val = $this->getSallyKey($key);
			if ($val !== null) return $val;
		}

		$data = $this->getContent();
		return $data === null ? null : (array_key_exists($key, $data) ? $data[$key] : null);
	}

	/**
	 * @param  string $subkey
	 * @return string
	 */
	public function getSallyKey($filename, $subkey = null) {
		$extra  = $this->getKey('extra', false);
		$subkey = self::EXTRA_SUBKEY;

		// nothing set
		if (!isset($extra[$subkey])) return null;

		$extra = $extra[$subkey];

		// return everything if requested
		if ($subkey === null) return $extra;

		return array_key_exists($subkey, $extra) ? $extra[$subkey] : null;
	}
}
