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
 * @since  0.9
 * @author zozi@webvariants.de
 */
class sly_Slice_RendererImpl implements sly_Slice_Renderer {
	protected $moduleService;

	/**
	 *
	 * @param sly_Service_Module $moduleService
	 */
	public function __construct(sly_Service_Module $moduleService) {
		$this->moduleService = $moduleService;
	}

	/**
	 *
	 * @param  sly_Model_ISlice  $slice
	 * @param  string            $dataIndex
	 * @return string
	 * @throws sly_Exception
	 */
	public function renderInput(sly_Model_ISlice $slice, $dataIndex) {
		if (!$this->moduleService->exists($slice->getModule())) {
			throw new sly_Exception('Module does not exists!');
		}

		$filename = $this->moduleService->getFolder().DIRECTORY_SEPARATOR.$this->moduleService->getInputFilename($slice->getModule());
		$values   = new sly_Slice_Values($slice->getValues());
		$form     = new sly_Slice_Form();

		ob_start();

		try {
			include $filename;

			if ($form instanceof sly_Form) {
				$form->setSubmitButton(null);
				$form->setResetButton(null);
				$form->setCsrfEnabled(false);

				print $form->render($dataIndex);
			}

			return ob_get_clean();
		}
		catch (Exception $e) {
			ob_end_clean();
			throw $e;
		}
	}

	/**
	 *
	 * @param sly_Model_ISlice $slice
	 * @return string
	 * @throws Exception
	 */
	public function renderOutput(sly_Model_ISlice $slice) {
		// Allow addOns to modify/switch the slice before rendering it.
		// If no slice instance is returned, then it's assumed that the slice
		// shall not be rendered and the event returned the desired output.
		// Use this to return cached content or "mute" a slice.

		$container  = sly_Core::getContainer();
		$dispatcher = $container->getDispatcher();
		$slice      = $dispatcher->filter('SLY_SLICE_PRE_RENDER', $slice, array(
			'module' => $slice->getModule(),
			'values' => $slice->getValues(),
		));

		if (!($slice instanceof sly_Model_ISlice)) {
			return is_string($slice) ? $slice : '';
		}

		$service  = $container->getModuleService();
		$filename = $service->getFolder().DIRECTORY_SEPARATOR.$service->getOutputFilename($slice->getModule());
		$values   = new sly_Slice_Values($slice->getValues());

		unset($service, $dispatcher, $container);
		ob_start();

		try {
			include $filename;

			$output = ob_get_clean();
			$output = sly_Util_HTML::replaceSallyLinks($output);
		}
		catch (Exception $e) {
			ob_end_clean();
			throw $e;
		}

		// Allow addOns to alter the output before it's returned.
		$container  = sly_Core::getContainer();
		$dispatcher = $container->getDispatcher();
		$output     = $dispatcher->filter('SLY_SLICE_POST_RENDER', $output, array(
			'slice'  => func_get_arg(0),
			'module' => $slice->getModule(),
			'values' => $slice->getValues()
		));

		return $output;
	}
}
