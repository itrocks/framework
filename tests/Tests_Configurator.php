<?php
namespace ITRocks\Framework\Tests;

use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Has_Get;

/**
 * Tests configurator
 */
class Tests_Configurator implements Configurable
{
	use Has_Get;

	//------------------------------------------------------------------------------- PHPUNIT_OPTIONS
	const PHPUNIT_OPTIONS = 'tests_phpunit_options';

	//------------------------------------------------------------------------------ $phpunit_options
	/**
	 * @var string[]
	 */
	public array $phpunit_options;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Certificate generator configuration
	 *
	 * @param $configuration array
	 */
	public function __construct(mixed $configuration = [])
	{
		$this->phpunit_options = $configuration[static::PHPUNIT_OPTIONS];
	}

}
