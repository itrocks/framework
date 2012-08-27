<?php

interface Controller
{

	//------------------------------------------------------------------------------------------ call
	/**
	 * @param array $args
	 * @param array $form
	 * @param array $files
	 */
	public function call($params, $form, $files);

}
