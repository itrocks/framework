<?php
namespace ITRocks\Framework\Tools\Files;

use ITRocks\Framework\Application;

/**
 * PDF files manipulation
 *
 * @depends poppler-utils
 */
class PDF
{

	//------------------------------------------------------------------------------------ $file_name
	/**
	 * @var string
	 */
	public string $file_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $file_name string|null
	 */
	public function __construct(string $file_name = null)
	{
		if (isset($file_name)) {
			$this->file_name = $file_name;
		}
	}

	//----------------------------------------------------------------------------------------- toPng
	/**
	 * Convert PDF file to PNG
	 *
	 * @param $resolution integer
	 * @requires apt install imagetools
	 * @return string[] png files names (one per converted page)
	 */
	public function toPng(int $resolution = 300) : array
	{
		$file_root    = str_replace(DOT, '-', uniqid('pdf-', true));
		$output_file  = Application::current()->getTemporaryFilesPath() . SL . $file_root;
		$options      = ['png', 'r' => $resolution];
		$text_options = '';
		foreach ($options as $option_name => $option_value) {
			if (is_numeric($option_name)) {
				$text_options .= SP . '-' . $option_value;
			}
			else {
				$text_options .= SP . '-' . $option_name . SP . $option_value;
			}
		}
		$command = 'pdftoppm' . $text_options . SP . DQ . $this->file_name . DQ . SP . $output_file;
		exec($command);
		return glob($output_file . '-*.png');
	}

	//----------------------------------------------------------------------------------------- toSvg
	/**
	 * Convert PDF file to SVG
	 *
	 * @requires apt install pdf2svg
	 * @return string[] svg files names (one per converted page)
	 */
	public function toSvg() : array
	{
		$file_root = Application::current()->getTemporaryFilesPath() . SL
			. str_replace(DOT, '-', uniqid('pdf-', true));
		$output_file = $file_root . '.svg';
		$command     = 'pdf2svg ' . DQ . $this->file_name . DQ . SP . $output_file;
		exec($command);
		if (is_file($output_file)) {
			return [$output_file];
		}
		return glob($file_root . '-*.svg');
	}

}
