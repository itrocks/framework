<?php
namespace ITRocks\Framework\Feature\List_\Search;

use ITRocks\Framework\Feature\List_\Search_Parameters_Parser;
use ITRocks\Framework\Plugin\Installable;
use ITRocks\Framework\Plugin\Installable\Installer;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;

/**
 * Search by contained keywords
 */
class Implicit_Jokers implements Installable, Registerable
{

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'Search by contained keywords';
	}

	//--------------------------------------------------------------------------------------- install
	/**
	 * @param $installer Installer
	 */
	public function install(Installer $installer)
	{
		$installer->addPlugin($this);
	}

	//---------------------------------------------------------------------------------- jokersAround
	/**
	 * @param $search_value string The value around which you add jokers (modified)
	 * @param $property     Reflection_Property
	 */
	public function jokersAround(&$search_value, Reflection_Property $property)
	{
		$type_string = $property->getType()->asString();
		if (in_array($type_string, [Type::STRING, Type::STRING_ARRAY], true)) {
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

}
