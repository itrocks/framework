<?php
namespace ITRocks\Framework\Dao\File\Session_File;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Tools\Image;
use ITRocks\Framework\Session;

/**
 * Session file output controller
 */
class Output_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Outputs an image directly into caller's output (echo)
	 *
	 * Parameters are :
	 * - 1st : file key (eg it's name, local to the current session)
	 * - 2st : 'size' => integer or directly the size (with no 'size' key)
	 *
	 * @example /ITRocks/Framework/Dao/File/Session_File/output/example.jpg/64
	 * @example /ITRocks/Framework/Dao/File/Session_File/output/example.jpg?size=64
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return null
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		/** @var $session_files Files */
		$raw_parameters = $parameters->getRawParameters();
		$file_key       = array_shift($raw_parameters);
		$session_files  = Session::current()->get(Files::class);
		// numeric (session files index) file key
		if (isset($session_files->files[$file_key])) {
			$file = $session_files->files[$file_key];
		}
		// string (session files filename) file key
		else {
			foreach ($session_files->files as $file) {
				if ($file->name === $file_key) {
					break;
				}
			}
		}
		// output
		if (isset($file)) {
			header('Content-Type: ' . $file->getType());
			$size = isset($raw_parameters['size'])
				? $raw_parameters['size']
				: array_shift($raw_parameters);
			if ($size) {
				$image = Image::createFromString($file->content);
				$image->resize($size, $size)->display();
			}
			else {
				echo $file->content;
			}
		}
		return;
	}

}
