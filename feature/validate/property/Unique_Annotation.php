<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use Exception;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Annotation\Template\Boolean_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * The unique annotation validator
 */
class Unique_Annotation extends Boolean_Annotation implements Property_Context_Annotation
{
	use Annotation;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    string
	 * @param $property Reflection_Property ie the contextual Reflection_Property object
	 */
	public function __construct($value, Reflection_Property $property)
	{
		parent::__construct($value);
		$this->property = $property;
	}

	//--------------------------------------------------------------------------------- reportMessage
	public function reportMessage(): string
	{
		return 'This value already exist';
	}

	//-------------------------------------------------------------------------------------- validate
	/**
	 * @param $object object
	 * @return Boolean
	 * @throws Exception
	 */
	public function validate($object): bool
	{
		$property_name = $this->property->getName();

		if(!property_exists($object, $property_name)) {
			throw new Exception(
				sprintf(
					'The %s property does not exist in %s object',
					$property_name,
					get_class($object)
				)
			);
		}

		if(!$object->{$property_name}) {
			return true;
		}

		$search = Dao::searchOne([$property_name => $object->{$property_name}], get_class($object));

		return $search === null;
	}

}
