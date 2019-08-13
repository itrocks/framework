<?php
namespace ITRocks\Framework\Feature;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Reflection_Class;

/**
 * Delete and replace
 */
class Delete_And_Replace
{

	//---------------------------------------------------------------------------------------- delete
	/**
	 * @param $object object
	 * @return boolean
	 */
	public function delete($object)
	{
		if (Dao::getObjectIdentifier($object)) {
			return Dao::delete($object);
		}
		return false;
	}

	//------------------------------------------------------------------------------ deleteAndReplace
	/**
	 * @param $replaced    object
	 * @param $replacement object
	 * @return boolean true if replacement and deletion have been done
	 */
	public function deleteAndReplace($replaced, $replacement)
	{
		if (Dao::getObjectIdentifier($replaced) && Dao::getObjectIdentifier($replacement)) {
			return
				$this->deleteComponents($replaced)
				&& $this->replace($replaced, $replacement)
				&& $this->delete($replaced);
		}
		return false;
	}

	//------------------------------------------------------------------------------ deleteComponents
	/**
	 * Delete single-object components
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $replaced object
	 * @return boolean true if component objects have all been purged
	 */
	protected function deleteComponents($replaced)
	{
		/** @noinspection PhpUnhandledExceptionInspection object */
		foreach ((new Reflection_Class($replaced))->getProperties() as $property) {
			/** @noinspection PhpUnhandledExceptionInspection */
			if (
				$property->getAnnotation('component')->value
				&& !$property->getType()->isMultiple()
				&& ($component = $property->getValue($replaced))
			) {
				if (!Dao::delete($component)) {
					return false;
				}
			}
		}
		return true;
	}

	//--------------------------------------------------------------------------------------- replace
	/**
	 * @param $replaced    object
	 * @param $replacement object
	 * @return boolean true if replacement has been done
	 */
	protected function replace($replaced, $replacement)
	{
		return Dao::replaceReferences($replaced, $replacement);
	}

}
