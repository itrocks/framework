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

	//------------------------------------------------------------------------------ LINES_TO_DISPLAY
	const LINES_TO_DISPLAY = 30;

	//------------------------------------------------------------------------- MAXIMUM_LINES_TO_SHOW
	const MAXIMUM_LINES_TO_SHOW = 30;

	//--------------------------------------------------------------------------- $allowed_by_default
	/**
	 * @var boolean
	 */
	private $allowed_by_default = true;

	//--------------------------------------------------------------------------- $features_exception
	/**
	 * @var string[]
	 */
	private $features_exception = [];

	//----------------------------------------------------------------------------- $lines_to_display
	/**
	 * @var integer
	 */
	public $lines_to_display;

	//------------------------------------------------------------------------ $maximum_lines_to_show
	/**
	 * @var integer
	 */
	public $maximum_lines_to_show;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration mixed
	 */
	public function __construct($configuration)
	{
		if (isset($configuration)) {
			$this->allowed_by_default = $configuration['allowed_by_default'];
			$this->features_exception = $configuration['features_exception'];
			$this->lines_to_display   = (isset($configuration['lines_to_display'])
				? $configuration['lines_to_display']
				: self::LINES_TO_DISPLAY
			);
			$this->maximum_lines_to_show = (isset($configuration['maximum_lines_to_show'])
				? $configuration['maximum_lines_to_show']
				: self::MAXIMUM_LINES_TO_SHOW
			);
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
	 * @param $class object
	 * @return boolean true if select all is allowed
	 */
	public function selectAllIsAllowed($class = null)
	{
		if ($class && (in_array(get_class($class), $this->features_exception))) {
			return !$this->allowed_by_default;
		}
		return $this->allowed_by_default;
	}

}
