<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Reflection_Class;

/**
 * allOf must read the parent annotations, if they do not have any @feature Feature name
 *
 * @implements Do_Not_Inherit
 */
trait Feature_Annotation
{

	//-------------------------------------------------------------------------------------- $context
	/**
	 * @var Reflection_Class
	 */
	protected static Reflection_Class $context;

	//----------------------------------------------------------------------------------------- allOf
	/**
	 * @param $reflection_object Reflection|Reflection_Class
	 * @param $context           Reflection|Reflection_Class|null for internal use
	 * @return static[]
	 */
	public static function allOf(
		Reflection|Reflection_Class $reflection_object, Reflection|Reflection_Class $context = null
	) : array
	{
		static::$context = $context ?: $reflection_object;

		/** @noinspection PhpMultipleClassDeclarationsInspection */
		/** @see Annotation::allOf */
		/** @var $this Annotation|static */
		$annotations = parent::allOf($reflection_object);

		$parents = $reflection_object->getTraits();
		if ($parent = $reflection_object->getParentClass()) {
			$parents[] = $parent;
		}

		foreach ($parents as $parent) {
			if (!static::hasFeatureAnnotation($parent)) {
				$annotations = array_merge(
					static::allOf($parent, $context ?: $reflection_object), $annotations
				);
			}
		}

		return $annotations;
	}

	//-------------------------------------------------------------------------- hasFeatureAnnotation
	/**
	 * @param $reflection_object Reflection_Class
	 * @return boolean
	 */
	private static function hasFeatureAnnotation(Reflection_Class $reflection_object) : bool
	{
		foreach (Class_\Feature_Annotation::allOf($reflection_object) as $feature_annotation) {
			if (ctype_upper(substr($feature_annotation->value, 0, 1))) {
				return true;
			}
		}
		return false;
	}

}
