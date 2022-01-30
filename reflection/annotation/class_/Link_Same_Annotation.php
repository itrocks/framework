<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Types_Annotation;
use ITRocks\Framework\Reflection\Link_Class;

/**
 * Identifies the class where to look for @composite for @link class with two identical classes
 */
class Link_Same_Annotation extends Annotation
{
	use Types_Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'link_same';

	//---------------------------------------------------------------------------------- getLinkClass
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return ?Link_Class
	 */
	public function getLinkClass() : ?Link_Class
	{
		/** @noinspection PhpUnhandledExceptionInspection annotation value must be a valid class name */
		return $this->value ? new Link_Class($this->value) : null;
	}

}
