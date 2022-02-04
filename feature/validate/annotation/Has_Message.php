<?php
namespace ITRocks\Framework\Feature\Validate\Annotation;

use ITRocks\Framework\Feature\Validate\Validator;

/**
 * For validator that get a specific message
 *
 * @extends Validator
 */
trait Has_Message
{

	//-------------------------------------------------------------------------------------- $message
	/**
	 * @var string
	 */
	protected string $message;

	//--------------------------------------------------------------------------------- reportMessage
	/**
	 * Gets the last validate() call resulting report message
	 *
	 * @return string
	 */
	public function reportMessage() : string
	{
		return $this->message;
	}

}
