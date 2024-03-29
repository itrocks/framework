<?php
namespace ITRocks\Framework\Mapper;

/**
 * Abstract_Class is used to read data for abstract classes / interfaces
 */
class Abstract_Class
{

	//---------------------------------------------------------------------------------------- $class
	/**
	 * Abstracts classes only store the instantiated class name
	 *
	 * @var string
	 */
	public string $class;

	//------------------------------------------------------------------------------- $representative
	/**
	 * @var string
	 */
	public string $representative;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->representative;
	}

}
