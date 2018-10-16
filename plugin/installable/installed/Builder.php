<?php
namespace ITRocks\Framework\Plugin\Installable\Installed;

use ITRocks\Framework\Plugin\Installable\Installed;

/**
 * An installed build (into builder.php)
 *
 * @store_name installed_builds
 */
class Builder extends Installed
{

	//---------------------------------------------------------------------------------- $added_class
	/**
	 * @var string
	 */
	public $added_class;

	//----------------------------------------------------------------------------------- $base_class
	/**
	 * @var string
	 */
	public $base_class;

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $base_class  string
	 * @param $added_class string
	 * @return static
	 */
	public function add($base_class, $added_class)
	{
		return $this->addProperties(['base_class' => $base_class, 'added_class' => $added_class]);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * @param $base_class  string
	 * @param $added_class string
	 * @return static
	 */
	public function remove($base_class, $added_class)
	{
		return $this->removeProperties(['base_class' => $base_class, 'added_class' => $added_class]);
	}

}
