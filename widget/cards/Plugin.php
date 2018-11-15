<?php
namespace ITRocks\Framework\Widget\Cards;

use ITRocks\Framework\Plugin\Installable;
use ITRocks\Framework\Plugin\Installable\Installer;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Reflection\Annotation\Parser;
use ITRocks\Framework\Widget\Cards\Annotation\Card_Columns_Annotation;
use ITRocks\Framework\Widget\Cards\Annotation\Card_Display_Annotation;
use ITRocks\Framework\Widget\Cards\Annotation\Card_Edit_Annotation;
use ITRocks\Framework\Widget\Cards\Annotation\Card_Groups_Annotation;
use ITRocks\Framework\Widget\Cards\Annotation\Card_Sums_Annotation;

/**
 * Display your documents into an interactive multiple dimensional cards viewer
 */
class Plugin implements Installable, Registerable
{

	//------------------------------------------------------------------------------------ __toString
	/**
	 * Returns the short description of the installable plugin
	 *
	 * It is the feature caption exactly how it will be displayed to the user
	 *
	 * @return string
	 */
	public function __toString()
	{
		return 'Display your documents into an interactive multiple dimensional cards viewer';
	}

	//--------------------------------------------------------------------------------------- install
	/**
	 * This code is called when the plugin is installed by the user
	 *
	 * Use the $installer parameter to install the components of your plugin.
	 *
	 * @param $installer Installer
	 */
	public function install(Installer $installer)
	{
		$installer->addPlugin($this);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->setAnnotations(Parser::T_CLASS, [
			'card_columns' => Card_Columns_Annotation::class,
			'card_display' => Card_Display_Annotation::class,
			'card_edit'    => Card_Edit_Annotation::class,
			'card_groups'  => Card_Groups_Annotation::class,
			'card_sums'    => Card_Sums_Annotation::class
		]);
	}

}
