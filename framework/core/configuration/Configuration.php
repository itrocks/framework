<?php
namespace SAF\Framework;

use Serializable;

/** @noinspection PhpIncludeInspection */ require_once "framework/core/mappers/Builder.php";
/** @noinspection PhpIncludeInspection */ require_once "framework/core/toolbox/Current.php";

/**
 * A configuration set : current configuration for the global application configuration, secondary configurations can be worked with
 */
class Configuration implements Serializable
{
	use Current { current as private pCurrent; }

	//------------------------------------------------------------------------------------------ $app
	/**
	 * @var string
	 */
	private $app;

	//--------------------------------------------------------------------------------------- $author
	/**
	 * @var string
	 */
	private $author;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Build configuration using configurations options
	 *
	 * Default configuration is set to the configuration if the "default" option is set to true.
	 *
	 * @param $configuration_options array recursive configuration array from the config.php file
	 */
	public function __construct($configuration_options)
	{
		foreach ($configuration_options as $name => $value) {
			$this->$name = $value;
		}
		if (isset($configuration_options["default"]) && $configuration_options["default"]) {
			self::current($this);
		}
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * Sets / gets current configuration
	 *
	 * When current configuration is set, each "class" property is changed into it's current object initialised with configuration parameters
	 *
	 * @param $set_current Configuration
	 * @return Configuration
	 */
	public static function current(Configuration $set_current = null)
	{
		if (isset($set_current)) {
			self::pCurrent($set_current);
			/** @noinspection PhpIncludeInspection */
			require_once Names::classToDirectory($set_current->app) . "/Application.php";
			/** @var $application Application */
			$application = Builder::create("SAF\\" . $set_current->app . "\\Application");
			Application::current($application);
			foreach ($set_current->getClassesConfigurations() as $class_name => $configuration) {
				if ($configuration == "@static") {
					$self_configuration = array();
					$set_current->$class_name =& $self_configuration;
					$class = new Reflection_Class($class_name);
					foreach ($class->accessProperties() as $property) if ($property->isStatic()) {
						$property_name = $property->name;
						$self_configuration[$property_name] =& $class_name::$$property_name;
					}
					$class->accessPropertiesDone();
				}
				if (class_uses_trait($class_name, 'SAF\Framework\Current')) {
					if (method_exists($class_name, "configure")) {
						$configuration = call_user_func(array($class_name, "configure"), $configuration);
					}
					$configuration_class_name = isset($configuration["class"])
						? $configuration["class"]
						: $class_name;
					$builder_class_name = $configuration_class_name . "_Builder_Configuration";
					if (class_exists($builder_class_name)) {
						/** @var $builder_object Configuration_Builder */
						$builder_object = Builder::create($builder_class_name);
						call_user_func(array($class_name, "current"), $builder_object->build($configuration));
					}
					else {
						call_user_func(
							array($class_name, "current"),
							Builder::create($configuration_class_name, array($configuration))
						);
					}
				}
				elseif (method_exists($class_name, "configure")) {
					call_user_func(array($class_name, "configure"), $configuration);
				}
				else {
					$self_configuration =& $set_current->$class_name;
					$class = new Reflection_Class($class_name);
					foreach ($class->accessProperties() as $property) {
						$property_name = $property->name;
						if ($property->isStatic() && isset($self_configuration[$property_name])) {
							$class_name::$$property_name =& $self_configuration[$property_name];
						}
					}
					$class->accessPropertiesDone();
				}
			}
			return $set_current;
		}
		else {
			return self::pCurrent($set_current);
		}
	}

	//----------------------------------------------------------------------- getApplicationClassName
	/**
	 * @return string
	 */
	public function getApplicationClassName()
	{
		return (isset($this->author) ? $this->author : "SAF") . "\\" . $this->app . "\\Application";
	}

	//---------------------------------------------------------------------------- getApplicationName
	/**
	 * Get the configuration's application name
	 *
	 * @return string
	 */
	public function getApplicationName()
	{
		return $this->app;
	}

	//---------------------------------------------------------------------- getClassesConfigurations
	/**
	 * Returns full configuration array for each class configuration
	 *
	 * @return array[]
	 */
	public function getClassesConfigurations()
	{
		$classes = array();
		foreach (get_object_vars($this) as $name => $value) {
			if (($name[0] >= "A") && ($name[0] <= "Z")) {
				$classes[$name] = $value;
			}
		}
		return $classes;
	}

	//------------------------------------------------------------------------------------- serialize
	/**
	 * Serialization compatible with unserialize()
	 *
	 * @return string
	 */
	public function serialize()
	{
		return serialize(get_object_vars($this));
	}

	//--------------------------------------------------------------------------------------- toArray
	/**
	 * Returns a configuration as an associative array like in config.php file
	 *
	 * @return array
	 */
	public function toArray()
	{
		return get_object_vars($this);
	}

	//----------------------------------------------------------------------------------- unserialize
	/**
	 * Current configuration is set once unserialized from session
	 *
	 * This enables loading of plugins before any other session class unserializing.
	 * If any current is already set, it is not overwritten so you can use serialization for other configuration objects.
	 *
	 * @param string $serialized
	 * @return void
	 */
	public function unserialize($serialized)
	{
		foreach (unserialize($serialized) as $key => $value) {
			$this->$key = $value;
		}
		$current = self::current();
		if (!isset($current)) {
			self::current($this);
		}
	}

}
