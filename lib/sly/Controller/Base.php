<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

/**
 * Base controller
 *
 * This is the base class for all controllers. It will determine the to-run
 * method (action), check permissions and instantiate the actual controller
 * object.
 *
 * All application controllers should inherit this one. Application controllers
 * are the ones for backend and frontend, not the actual "working" controllers
 * for addOns and backend/frontend pages.
 *
 * @ingroup controller
 * @author  Zozi
 * @since   0.1
 */
abstract class sly_Controller_Base {
	protected $request   = null; ///< sly_Request    the current request
	protected $container = null; ///< sly_Container  the DI container

	/**
	 * Set DI container
	 *
	 * This method is called by the application before the action is executed.
	 *
	 * @param sly_Container $container  the container the controller should use
	 */
	public function setContainer(sly_Container $container) {
		$this->container = $container;
	}

	/**
	 * get DI container
	 *
	 * @return sly_Container
	 */
	public function getContainer() {
		return $this->container;
	}

	/**
	 * Set request
	 *
	 * This method is called by the application before the action is executed.
	 *
	 * @param sly_Request $request  the request the controller should act upon
	 */
	public function setRequest(sly_Request $request) {
		$this->request = $request;
	}

	/**
	 * get request
	 *
	 * @return sly_Request
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * Render a view
	 *
	 * This method renders a view, making all keys in $params available as
	 * variables.
	 *
	 * @param  string  $filename      the filename to include, relative to the view folder
	 * @param  array   $params        additional parameters (become variables)
	 * @param  boolean $returnOutput  set to false to not use an output buffer
	 * @return string                 the generated output if $returnOutput, else null
	 */
	protected function render($filename, array $params = array(), $returnOutput = true) {
		unset($filename, $returnOutput);

		if (!empty($params)) {
			unset($params);
			extract(func_get_arg(1));
		}
		else {
			unset($params);
		}

		// func_get_arg() does not return the default argument, so we have to check
		// how many arguments were passed.

		if (func_num_args() < 3 || func_get_arg(2)) ob_start();
		include $this->getViewFolder().func_get_arg(0);
		if (func_num_args() < 3 || func_get_arg(2)) return ob_get_clean();
	}

	/**
	 * Get view folder
	 *
	 * Controllers must implement this method to specify where its view files
	 * are located. In most cases, since you will actually inherit the backend
	 * controller, this is already done. If you need to include many, many views,
	 * you might want to override this method to keep your view filenames short.
	 *
	 * @return string  the path to the view files
	 */
	abstract protected function getViewFolder();
}
