<?php
namespace SAF\Framework\Widget\Validate\Annotation;

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
	protected $message;

	//--------------------------------------------------------------------------------- reportMessage
	/**
	 * Gets the last validate() call resulting report message
	 *
	 * @return string
	 */
	public function reportMessage()
	{
		return $this->message;
	}

}
