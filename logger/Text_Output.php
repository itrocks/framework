<?php
namespace ITRocks\Framework\Logger;

/**
 * Text output logger
 */
class Text_Output
{

	//---------------------------------------------------------------------------------------- $quiet
	/**
	 * @var boolean
	 */
	private $quiet = false;

	//-------------------------------------------------------------------------------------- $started
	/**
	 * @var boolean true is start as been called
	 */
	private $started = false;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $quiet boolean
	 */
	public function __construct($quiet = false)
	{
		$this->quiet = $quiet;
	}

	//------------------------------------------------------------------------------------------- end
	/**
	 * End prints
	 */
	public function end()
	{
		if ($this->quiet) {
			return;
		}

		if ($_SERVER['REMOTE_ADDR'] !== 'console') {
			echo '</body></html>';
		}
		ob_flush();
		flush();
	}

	//------------------------------------------------------------------------------------------- log
	/**
	 * Prints given message
	 *
	 * @param $message  string
	 * @param $new_line boolean
	 */
	public function log($message = null, $new_line = true)
	{
		if ($this->quiet) return;

		if (!$this->started) {
			$this->start();
		}
		if ($message) {
			echo $message . ($new_line ? LF : '');
		}

		// Then output the result on-the-flow.
		ob_flush();
		flush();
	}

	//----------------------------------------------------------------------------------------- start
	/**
	 * Prints html head
	 */
	protected function start()
	{
		if ($this->quiet) {
			return;
		}

		$this->started = true;
		if ($_SERVER['REMOTE_ADDR'] !== 'console') {
			echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>ITRocks Unit testing</title>
	<style type="text/css" media="screen">
		html {font-family: Verdana, Arial, sans-serif; }
		body {white-space: pre; }
	</style>
</head>
<body>
EOT;
		}

		if (ob_get_length() === false) {
			ob_start();
		}
	}

}
