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
 * Business Model for Slices
 *
 * @author  zozi@webvariants.de
 * @ingroup model
 */
class sly_Model_Slice extends sly_Model_Base_Id implements sly_Model_ISlice {
	protected $module;            ///< string
	protected $serialized_values; ///< array

	protected $_attributes = array('module' => 'string', 'serialized_values' => 'array'); ///< array

	/**
	 * @return string
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * @param string $module
	 */
	public function setModule($module) {
		$this->module = $module;
	}

	/**
	 * @param  string $finder
	 * @param  string $value
	 */
	public function setValue($finder, $value = null) {
		$this->serialized_values[$finder] = $value;
	}

	/**
	 * @param  string $type
	 * @param  string $finder
	 * @return mixed
	 */
	public function getValue($finder, $default = null) {
		return isset($this->serialized_values[$finder]) ? $this->serialized_values[$finder] : $default;
	}

	public function setValues($values = array()) {
		if (!sly_Util_Array::isAssoc($values)) {
			throw new sly_Exception('Values must be assoc array!');
		}

		$this->serialized_values = sly_makeArray($values);
	}

	public function getValues() {
		return $this->serialized_values;
	}

	/**
	 * render (execute) this slice's module
	 *
	 * @return string
	 */
	public function getOutput() {
		$renderer = sly_Core::getContainer()->get('sly-slice-renderer');

		return $renderer->renderOutput($this);
	}
}
