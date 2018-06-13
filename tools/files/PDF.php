<?php
namespace ITRocks\Framework\Tools\Files;

/**
 * PDF files manipulation
 */
class PDF
{

	//------------------------------------------------------------------------------------ $file_name
	/**
	 * @var string
	 */
	public $file_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $file_name string
	 */
	public function __construct($file_name = null)
	{
		if (isset($file_name)) {
			$this->file_name = $file_name;
		}
	}

	//----------------------------------------------------------------------------------------- toPng
	/**
	 * Convert PDF file to PNG
	 *
	 * @requires apt install imagetools
	 * @return string[] png files names (one per converted page)
	 */
	public function toPng()
	{
		$file_root   = str_replace(DOT, '-', uniqid('pdf-', true));
		$output_file = '/tmp/' . $file_root . '.png';
		$options = [
			'background' => 'white',
			'density'    => 300,
			'layers'     => 'flatten',
			'quality'    => '100%'
		];
		$text_options = '';
		foreach ($options as $option_name => $option_value) {
			$text_options .= SP . '-' . $option_name . SP . $option_value;
		}
		$command = 'convert' . $text_options . SP . DQ . $this->file_name . DQ . SP . $output_file;
		exec($command);
		if (is_file($output_file)) {
			return [$output_file];
		}
		return glob('/tmp/' . $file_root . '-*.png');
	}

	//----------------------------------------------------------------------------------------- toSvg
	/**
	 * Convert PDF file to SVG
	 *
	 * @requires apt install pdf2svg
	 * @return string[] svg files names (one per converted page)
	 */
	public function toSvg()
	{
		$file_root = str_replace(DOT, '-', uniqid('pdf-', true));
		$output_file = '/tmp/' . $file_root . '.svg';
		$command = 'pdf2svg ' . DQ . $this->file_name . DQ . SP . $output_file;
		exec($command);
		if (is_file($output_file)) {
			return [$output_file];
		}
		return glob('/tmp/' . $file_root . '-*.svg');
	}

}
