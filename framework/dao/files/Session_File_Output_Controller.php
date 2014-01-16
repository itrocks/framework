<?php
namespace SAF\Framework;

/**
 * Session file output controller
 */
class Session_File_Output_Controller implements Feature_Controller
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
		/** @var $session_files Session_Files */
		$raw_parameters = $parameters->getRawParameters();
		$file_key = reset($raw_parameters);
		$session_files = Session::current()->get('SAF\Framework\Session_Files');
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
			header("Content-Type: " . $file->getType());
			if (isset($raw_parameters["size"])) {
				$image = Image::createFromString($file->content);
				$image->resize($raw_parameters["size"], $raw_parameters["size"])->display();
			}
			else {
				echo $file->content;
			}
		}
	}

}
