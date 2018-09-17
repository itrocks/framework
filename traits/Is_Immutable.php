<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Data_Link\Identifier_Map;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;

/**
 * Is_Immutable : Allow to manage storage of class only if exactly same values don't exist in Table
 *
 * @before_write beforeWriteOfImmutable
 */
trait Is_Immutable
{

	//------------------------------------------------------------------------ beforeWriteOfImmutable
	/**
	 * Called before write, this ensures that the object will be immutable into the data link
	 *
	 * Ignores the object identifier, and identifies it only with its property values :
	 * - If an object with the same property values exist in data store, then it will be linked to it
	 * - If it is a new object, it will created
	 *
	 * @param $link Data_Link
	 */
	public function beforeWriteOfImmutable(Data_Link $link = null)
	{
		if (!$link) {
			$link = Dao::current();
		}
		if (!($link instanceof Identifier_Map)) {
			return;
		}

		// TODO this "form cleanup" code must be generalized into a cleanup plugin
		$search = Search_Object::create(get_class($this));
		foreach ((new Reflection_Class(get_class($this)))->getProperties() as $property) {
			if (
				!$property->isStatic()
				&& !Store_Annotation::of($property)->isFalse()
				&& $property->getAnnotation('immutable')->value
				&& ($value = $property->getValue($this))
			) {
				if (is_string($value)) {
					$clean_value = preg_replace('#\s+#', ' ', trim($value));
					if ($clean_value !== $value) {
						$value = $clean_value;
						$property->setValue($this, $value);
					}
				}
				$property->setValue($search, is_null($value) ? Func::isNull() : Func::equal($value));
			}
		}

		$link->disconnect($this);
		if ($existing = $link->searchOne($search)) {
			$link->replace($this, $existing, false);
		}
	}

}
