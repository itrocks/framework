<?php
namespace ITRocks\Framework\Dao\File\Session_File;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Image;

/**
 * Session file output controller
 *
 * Outputs an image file with resizing
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
		$raw_parameters = $parameters->getRawParameters();
		if (is_numeric(reset($raw_parameters)) && ctype_upper(substr(key($raw_parameters), 0, 1))) {
			$raw_parameters = array_merge(
				[key($raw_parameters), current($raw_parameters)],
				array_slice($raw_parameters, 1)
			);
		}
		$file_key      = array_shift($raw_parameters);
		$session_files = Session::current()->get(Files::class, true);
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
			header('Content-Disposition: inline; filename=' . DQ . $file->name . DQ);
			header('Content-Type: ' . $file->getType());
			$height = isset($raw_parameters['height']) ? $raw_parameters['height'] : null;
			$rotate = isset($raw_parameters['rotate']) ? $raw_parameters['rotate'] : null;
			$width  = isset($raw_parameters['width'])  ? $raw_parameters['width']  : null;
			if ($height || $width || $rotate) {
				$image = Image::createFromString($file->content);
				if ($height || $width) {
					$image = $image->resize($width, $height);
				}
				if ($rotate) {
					$image = $image->rotate($rotate);
				}
				$image->display();
			}
			else {
				$size = isset($raw_parameters['size'])
					? $raw_parameters['size']
					: array_shift($raw_parameters);
				if ($size && !$file->getType()->is('svg')) {
					$image = Image::createFromString($file->content);
					$image->resize($size, $size)->display();
				}
				else {
					echo $file->content;
				}
			}
		}
		return;
	}

}
