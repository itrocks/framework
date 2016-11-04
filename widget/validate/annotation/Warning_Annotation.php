<?php
namespace ITRocks\Framework\Widget\Validate\Annotation;

use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Widget\Validate\Annotation;
use ITRocks\Framework\Widget\Validate\Result;

/**
 * Common code for all @warning annotation
 */
abstract class Warning_Annotation extends Method_Annotation
{
	use Annotation;
	use Has_Message;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'warning';

	//------------------------------------------------------------------------------- checkCallReturn
	/**
	 * @param $result string
	 * @return string
	 */
	protected function checkCallReturn($result)
	{
		$this->message = is_string($result) ? $result : Result::VALID;
		if ($this->message) {
			return $this->valid = Result::WARNING;
		}
		return Result::NONE;
	}

}
