<?php
namespace ITRocks\Framework\Dao\Gaufrette;

use Gaufrette\Adapter;
use Gaufrette\Filesystem;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection\Reflection_Method;
use ITRocks\Framework\Traits\Has_Name;
use ReflectionException;

/**
 * Contains a configured gaufrette Filesystem with a name
 *
 * @package ITRocks\Framework\Dao\Gaufrette
 */
class File_System
{
	use Has_Name;

	//----------------------------------------------------------------------------------- $filesystem
	/**
	 * The knplabs/gaufrette filesystem instantiated with its adapter and configuration arguments
	 *
	 * @var Filesystem
	 */
	public $filesystem;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocMissingThrowsInspection ReflectionException
	 * @param $name                  string
	 * @param $adapter_configuration array see Link::$configuration for the format
	 * @throws Exception
	 */
	public function __construct($name, $adapter_configuration)
	{
		$adapter_class     = $adapter_configuration['adapter_class_name'];
		$adapter_arguments = [];
		/** @noinspection PhpUnhandledExceptionInspection $adapter_class must be valid */
		$parameters = (new Reflection_Method($adapter_class, '__construct'))->getParameters();
		if (isset($adapter_configuration['arguments'])) {
			foreach ($parameters as $key => $reflection_parameter) {
				if (isset($adapter_configuration['arguments'][$key])) {
					$adapter_arguments[$key] = $adapter_configuration['arguments'][$key];
				}
				else {
					if ($reflection_parameter->isDefaultValueAvailable()) {
						/** @noinspection PhpUnhandledExceptionInspection isDefaultValueAvailable */
						$adapter_arguments[$key] = $reflection_parameter->getDefaultValue();
					}
					else {
						throw new Exception(
							'Configuration error for gaufrette adapter : ' . $adapter_class
							. DOT . $key . ' is a mandatory argument. You should define a value for it.'
						);
					}
				}
			}
		}

		$this->name = $name;
		try {
			/** @var $adapter Adapter */
			$adapter          = Builder::create($adapter_class, $adapter_arguments);
			$this->filesystem = new Filesystem($adapter);
		}
		catch (ReflectionException $exception) {
			throw new Exception("Cannot build adapter $name. See configuration");
		}
		finally {
			if (!$this->filesystem) {
				throw new Exception("Cannot build adapter $name. See configuration");
			}
		}
	}

}
