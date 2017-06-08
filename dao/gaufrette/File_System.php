<?php
namespace ITRocks\Framework\Dao\Gaufrette;

use Exception;
use Gaufrette\Filesystem;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection\Reflection_Method;
use ITRocks\Framework\Traits\Has_Name;

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
	 * @param $name                  string
	 * @param $adapter_configuration array see Link::$configuration for the format
	 * @throws Exception
	 */
	public function __construct($name, $adapter_configuration)
	{
		$adapter_class     = $adapter_configuration['adapter_class_name'];
		$adapter_arguments = [];
		$parameters        = (new Reflection_Method($adapter_class, '__construct'))->getParameters();
		if (isset($adapter_configuration['arguments'])) {
			foreach ($parameters as $key => $reflection_parameter) {
				if (isset($adapter_configuration['arguments'][$key])) {
					$adapter_arguments[$key] = $adapter_configuration['arguments'][$key];
				}
				else {
					if ($reflection_parameter->isDefaultValueAvailable()) {
						$adapter_arguments[$key] = $reflection_parameter->getDefaultValue();
					}
					else {
						throw new Exception('Configuration error for gaufrette adapter : ' . $adapter_class
							. $key . ' is a mandatory argument. You should define a value for it.');
					}
				}
			}
		}

		$this->name = $name;
		try {
			$this->filesystem = new Filesystem(Builder::create($adapter_class, $adapter_arguments));
		}
		catch (Exception $exception) {
			throw new Exception("Cannot build adapter $name. See configuration");
		}
		finally {
			if (!$this->filesystem) {
				throw new Exception("Cannot build adapter $name. See configuration");
			}
		}
	}

}
