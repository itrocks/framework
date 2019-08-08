<?php
namespace ITRocks\Framework\Feature\List_;

use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Has_Get;

/**
 * Plugin : Allow to enable select_all capabilities
 */
class Select_All implements Configurable
{
	use Has_Get;

	//------------------------------------------------------------------------------- DISPLAYED_LINES
	const DISPLAYED_LINES = 20;

	//----------------------------------------------------------------------- MAXIMUM_DISPLAYED_LINES
	const MAXIMUM_DISPLAYED_LINES = 20;

	//--------------------------------------------------------------------------- $allowed_by_default
	/**
	 * @var boolean
	 */
	private $allowed_by_default = true;

	//------------------------------------------------------------------------------ $displayed_lines
	/**
	 * @var integer
	 */
	public $displayed_lines = self::DISPLAYED_LINES;

	//--------------------------------------------------------------------------- $features_exception
	/**
	 * @var string[]
	 */
	private $features_exception = [];

	//---------------------------------------------------------------------- $maximum_displayed_lines
	/**
	 * @var integer
	 */
	public $maximum_displayed_lines = self::MAXIMUM_DISPLAYED_LINES;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array
	 */
	public function __construct($configuration)
	{
		if (isset($configuration)) {
			foreach ($configuration as $property_name => $value) {
				$this->$property_name = $value;
			}
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'select all configuration';
	}

	//---------------------------------------------------------------------------- selectAllIsAllowed
	/**
	 * Test if current controller class has selection limitation
	 *
	 * @param $object object
	 * @return boolean true if select all is allowed
	 */
	public function selectAllIsAllowed($object = null)
	{
		if ($object && in_array(get_class($object), $this->features_exception)) {
			return !$this->allowed_by_default;
		}
		return $this->allowed_by_default;
	}

}
