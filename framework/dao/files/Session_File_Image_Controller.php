<?php
namespace SAF\Framework;

/**
 * Session file image controller
 */
class Session_File_Image_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$objects = $parameters->getObjects();
		$objects['link'] = '/Session_File/output/' . reset($objects);
		return View::run($objects, $form, $files, Session_File::class, 'image');
	}

}
