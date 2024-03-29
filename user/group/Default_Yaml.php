<?php
namespace ITRocks\Framework\User\Group;

use ITRocks\Framework\Controller;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;

/**
 * Default yaml file generator
 */
class Default_Yaml
{

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var string
	 */
	private string $class;

	//--------------------------------------------------------------------------- $collection_classes
	/**
	 * @var string[]
	 */
	private array $collection_classes;

	//--------------------------------------------------------------------------------- $dependencies
	/**
	 * @var string[]
	 */
	private array $dependencies;

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * @var string
	 */
	private string $feature;

	//----------------------------------------------------------------------------- $features_classes
	/**
	 * @var string[]
	 */
	private array $features_classes;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class   string|null
	 * @param $feature string|null
	 */
	public function __construct(string $class = null, string $feature = null)
	{
		if (isset($class)) {
			$this->class = $class;
		}
		if (isset($feature)) {
			$this->feature = $feature;
		}
	}

	//--------------------------------------------------------------------- addCollectionDependencies
	/**
	 * @param $features   string[]
	 * @param $class_name string
	 */
	private function addCollectionDependencies(array $features, string $class_name) : void
	{
		if (!isset($this->collection_classes[$class_name])) {
			$this->collection_classes[$class_name] = true;
			$this->getDependencies($features, $class_name);
		}
	}

	//------------------------------------------------------------------------- addObjectDependencies
	/**
	 * @param $features   string[]
	 * @param $class_name string
	 */
	private function addObjectDependencies(array $features, string $class_name) : void
	{
		if (!isset($this->features_classes[$class_name])) {
			$this->features_classes[$class_name] = true;
			foreach ($features as $feature) {
				$path = str_replace(BS, SL, $class_name) . SL . $feature;
				$this->dependencies[$path] = $path;
			}
		}
	}

	//------------------------------------------------------------------------------- getDependencies
	/**
	 * Get low-level features for dependencies
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $features   string[]
	 * @param $class_name string|null
	 * @return string[]
	 */
	private function getDependencies(array $features, string $class_name = null) : array
	{
		if (!isset($class_name)) {
			$this->features_classes = [];
			$this->dependencies = [];
			$class_name = $this->class;
		}
		if (class_exists($class_name)) {
			/** @noinspection PhpUnhandledExceptionInspection class_exists */
			$class = new Reflection_Class($class_name);
			foreach ($class->getProperties([T_EXTENDS, T_USE]) as $property) {
				$link = Link_Annotation::of($property);
				if ($link->value) {
					if ($link->is(Link_Annotation::MAP, Link_Annotation::OBJECT)) {
						$this->addObjectDependencies(
							$features, $property->getType()->getElementTypeAsString()
						);
					}
					elseif ($link->isCollection()) {
						$this->addCollectionDependencies(
							$features, $property->getType()->getElementTypeAsString()
						);
					}
				}
			}
		}
		return $this->dependencies;
	}

	//---------------------------------------------------------------------------------------- toYaml
	/**
	 * Initialises $this->yaml with implicit data.
	 * Called when no file was found for an implicit feature.
	 *
	 * @return false|Yaml
	 */
	public function toYaml() : bool|Yaml
	{
		$feature = $this->feature;
		if (in_array($feature, Feature::ADMIN)) {
			$yaml = new Yaml(Yaml::defaultFileName(Controller\Feature::F_ADMIN));
			$yaml->extendYaml();
		}
		elseif (in_array($feature, Feature::API)) {
			$yaml = new Yaml(Yaml::defaultFileName(Controller\Feature::F_API));
			$yaml->extendYaml();
		}
		elseif (in_array($feature, Feature::EDIT)) {
			$yaml = new Yaml(Yaml::defaultFileName(Controller\Feature::F_EDIT));
			$yaml->extendYaml();
			foreach ($this->getDependencies([Controller\Feature::F_JSON]) as $feature) {
				$yaml->addFeature($feature);
			}
		}
		elseif (in_array($feature, Feature::EXPORT)) {
			$yaml = new Yaml(Yaml::defaultFileName(Controller\Feature::F_EXPORT));
			$yaml->extendYaml();
		}
		elseif (in_array($feature, Feature::IMPORT)) {
			$yaml = new Yaml(Yaml::defaultFileName(Controller\Feature::F_IMPORT));
			$yaml->extendYaml();
		}
		elseif (in_array($feature, Feature::JSON)) {
			$yaml = new Yaml(Yaml::defaultFileName(Controller\Feature::F_JSON));
			$yaml->extendYaml();
		}
		elseif (in_array($feature, Feature::OUTPUT)) {
			$yaml = new Yaml(Yaml::defaultFileName(Controller\Feature::F_OUTPUT));
			$yaml->extendYaml();
		}
		elseif (in_array($feature, Feature::F_PRINT)) {
			$yaml = new Yaml(Yaml::defaultFileName(Controller\Feature::F_PRINT));
			$yaml->extendYaml();
		}
		else {
			$yaml = false;
		}
		return $yaml;
	}

}
