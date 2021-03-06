<?php
namespace ITRocks\Framework\Feature\List_\Search;

use ITRocks\Framework\Feature\List_\Search_Parameters_Parser;
use ITRocks\Framework\Feature\List_\Search_Parameters_Parser\Words;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;

/**
 * @feature Search by contained keywords
 * @feature_exclude Starts_With
 */
class Implicit_Jokers implements Registerable
{
	use Has_Get;

	//-------------------------------------------------------------------------------------- $enabled
	/**
	 * @var boolean
	 */
	private $enabled = true;

	//---------------------------------------------------------------------------------- jokersAround
	/**
	 * @param $search_value string The value around which you add jokers (modified)
	 * @param $property     Reflection_Property
	 */
	public function jokersAround(string &$search_value, Reflection_Property $property)
	{
		if (!$this->enabled || Words::meansEmpty($search_value)) {
			return;
		}
		$type        = $property->getType();
		$type_string = $type->asString();
		if (
			in_array($type_string, [Type::STRING, Type::STRING_ARRAY], true)
			|| ($type->isClass() && !$type->isDateTime())
		) {
			$search_value = beginsWith($search_value, '=')
				? substr($search_value, 1)
				: str_replace('**', '*', ('*' . $search_value . '*'));
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->beforeMethod(
			[Search_Parameters_Parser::class, 'applySingleValue'], [$this, 'jokersAround']
		);
	}

	//------------------------------------------------------------------------------------ setEnabled
	/**
	 * @param $enabled boolean
	 * @return boolean
	 */
	public function setEnabled($enabled = true)
	{
		$last_enabled  = $this->enabled;
		$this->enabled = $enabled;
		return $last_enabled;
	}

}
