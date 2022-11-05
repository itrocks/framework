<?php
namespace ITRocks\Framework\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Base methods to use in a unit test class
 */
abstract class Testable extends TestCase
{

	//------------------------------------------------------------------------------------------- ALL
	const ALL = 'all';

	//---------------------------------------------------------------------------------------- ERRORS
	const ERRORS = 'errors';

	//------------------------------------------------------------------------------------------ NONE
	const NONE = 'none';

	//--------------------------------------------------------------------------------- $errors_count
	/**
	 * @var integer
	 */
	public int $errors_count = 0;

	//--------------------------------------------------------------------------------------- $header
	/**
	 * Header content to show if an error comes when $show_when_ok is false
	 * Reset once shown
	 *
	 * @var string
	 */
	public string $header = '';

	//----------------------------------------------------------------------------------------- $show
	/**
	 * @values all, errors, none
	 * @var string
	 */
	public string $show = self::ERRORS;

	//---------------------------------------------------------------------------------- $tests_count
	/**
	 * @var integer
	 */
	public int $tests_count = 0;

	//----------------------------------------------------------------------------------------- begin
	/**
	 * Begin of a unit test class
	 */
	public function begin() : void
	{
		$this->show('<h3>' . get_class($this) . '</h3>' . LF . '<ul>' . LF);
	}

	//------------------------------------------------------------------------------------------- end
	/**
	 * End of a unit test class
	 */
	public function end() : void
	{
		$this->show('</ul>' . LF);
	}

	//----------------------------------------------------------------------------------------- flush
	public function flush() : void
	{
		echo $this->header;
		$this->header = '';
	}

	//---------------------------------------------------------------------------------------- method
	/**
	 * Start test method log
	 *
	 * @deprecated PhpUnit already register method name
	 * @param $method_name string
	 */
	public function method(string $method_name) : void
	{
		$this->show('<h4>' . $method_name . '</h4>' . LF);
	}

	//------------------------------------------------------------------------------------------ show
	/**
	 * @param $show string
	 */
	protected function show(string $show) : void
	{
		if (($this->show === self::ALL) || ($this->errors_count && ($this->show === self::ERRORS))) {
			echo $show;
		}
		elseif ($this->show === self::ERRORS) {
			$this->header .= $show;
		}
	}

}
