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
 * @ingroup database
 */
class sly_DB_PDO_Driver_PGSQL extends sly_DB_PDO_Driver {
	/**
	 * @return string
	 */
	public function getDSN() {
		$dsn = 'pgsql:host='.$this->host;
		if(!empty($this->database)) $dsn .=';dbname='.$this->database;
		return $dsn;
	}

	/**
	 * @param  string $name  the database name
	 * @return string
	 */
	public function getCreateDatabaseSQL($name) {
		return 'CREATE DATABASE `'.$name.'` WITH ENCODING \'UNICODE\'';
	}

	/**
	 * @return array
	 */
	public function getVersionConstraints() {
		return array(
			sly_Util_Requirements::OK      => '8.0',
			sly_Util_Requirements::WARNING => '8.0'
		);
	}
}
