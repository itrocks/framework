<?php

interface Controller
{

	//------------------------------------------------------------------------------------------ call
	/**
	 * @param array $params
	 * @param array $form
	 * @param array $files
	 */
	public function call($params, $form, $files);

}
