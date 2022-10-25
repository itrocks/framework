<?php
namespace ITRocks\Framework\Controller\Tests;

use ITRocks\Framework;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Tests;
use ITRocks\Framework\View;
use ITRocks\Framework\View\View_Exception;

/**
 * A working controller
 */
class Controller implements Framework\Controller
{

	//--------------------------------------------------------------------------------------- CRASHED
	const CRASHED = 'CRASHED';

	//-------------------------------------------------------------------------------------- MULTIPLE
	const MULTIPLE = 'multiple';

	//--------------------------------------------------------------------------------------- WORKING
	const WORKING = 'working';

	//----------------------------------------------------------------------------------- runCrashing
	/**
	 * A crashing controller
	 *
	 * @return string
	 * @throws View_Exception
	 */
	public function runCrashing() : string
	{
		throw new View_Exception(self::CRASHED);
		/** @noinspection PhpUnreachableStatementInspection This is a test */
		return 'NOT-CRASHING';
	}

	//----------------------------------------------------------------------------------- runMultiple
	/**
	 * A multiple controller : calls a view that includes :
	 * - a working controller
	 * - then a crashing controller
	 * - at least a working controller
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string
	 */
	public function runMultiple(Parameters $parameters, array $form, array $files) : ?string
	{
		return trim(View::run($parameters->getObjects(), $form, $files, Tests::class, self::MULTIPLE));
	}

	//------------------------------------------------------------------------------------ runWorking
	/**
	 * A working controller : the view returns 'WORKING'
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return ?string
	 */
	public function runWorking(Parameters $parameters, array $form, array $files) : ?string
	{
		return trim(View::run($parameters->getObjects(), $form, $files, Tests::class, self::WORKING));
	}

}
