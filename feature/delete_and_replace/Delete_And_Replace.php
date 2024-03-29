<?php
namespace ITRocks\Framework\Feature;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Plugin;
use ITRocks\Framework\Reflection\Attribute\Property\Component;
use ITRocks\Framework\Reflection\Reflection_Class;

/**
 * Delete and replace
 */
class Delete_And_Replace implements Plugin
{

	//---------------------------------------------------------------------------------------- delete
	/**
	 * @param $object object
	 * @return boolean
	 */
	public function delete(object $object) : bool
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
	public function deleteAndReplace(object $replaced, object $replacement) : bool
	{
		if (Dao::getObjectIdentifier($replaced) && Dao::getObjectIdentifier($replacement)) {
			return
				$this->deleteComponents($replaced, $replacement)
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
	 * @param $replacement object
	 * @return boolean true if component objects have all been purged
	 */
	protected function deleteComponents(object $replaced, object $replacement) : bool
	{
		/** @noinspection PhpUnhandledExceptionInspection object */
		foreach ((new Reflection_Class($replaced))->getProperties() as $property) {
			/** @noinspection PhpUnhandledExceptionInspection */
			if (
				Component::of($property)?->value
				&& !$property->getType()->isMultiple()
				&& ($component = $property->getValue($replaced))
			) {
				/** @noinspection PhpUnhandledExceptionInspection */
				$replacement_component = $property->getValue($replacement);
				if (
					$replacement_component
						? !$this->deleteAndReplace($component, $replacement_component)
						: !Dao::delete($component)
				) {
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
	protected function replace(object $replaced, object $replacement) : bool
	{
		return Dao::replaceReferences($replaced, $replacement);
	}

}
