<?php
namespace ITRocks\Framework\Plugin\Installable\Installed;

use ITRocks\Framework\Plugin\Installable\Installed;
use ITRocks\Framework\Reflection\Attribute\Class_\Store_Name;

/**
 * An installed build (into builder.php)
 */
#[Store_Name('installed_builds')]
class Builder extends Installed
{

	//---------------------------------------------------------------------------------- $added_class
	/**
	 * @var string
	 */
	public string $added_class;

	//----------------------------------------------------------------------------------- $base_class
	/**
	 * @var string
	 */
	public string $base_class;

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $base_class  string
	 * @param $added_class string
	 * @return static
	 */
	public function add(string $base_class, string $added_class) : static
	{
		return $this->addProperties(['base_class' => $base_class, 'added_class' => $added_class]);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * @param $base_class  string
	 * @param $added_class string
	 * @return ?static
	 */
	public function remove(string $base_class, string $added_class) : ?static
	{
		return $this->removeProperties(['base_class' => $base_class, 'added_class' => $added_class]);
	}

}
