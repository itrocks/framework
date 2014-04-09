<?php
namespace SAF\Framework\Widget;

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
			if ($this->replace($replaced, $replacement)) {
				if ($this->delete($replaced)) {
					return true;
				}
			}
		}
		return false;
	}

	//--------------------------------------------------------------------------------------- replace
	/**
	 * @param $replaced    object
	 * @param $replacement object
	 * @return boolean true if replacement has been done
	 */
	public function replace($replaced, $replacement)
	{
		return Dao::replaceReferences($replaced, $replacement);
	}

}
