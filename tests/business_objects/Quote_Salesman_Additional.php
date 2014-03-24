<?php
namespace SAF\Tests;

/**
 * A salesman with specific data for it's link to a quote and with additional specific data
 *
 * The 'link' annotation allow to consider this class as a link class
 *
 * @link Quote_Salesman
 * @set Quotes_Salesmen_Additional
 */
class Quote_Salesman_Additional extends Quote_Salesman
{

	//------------------------------------------------------------------------------ $additional_text
	/**
	 * @var string
	 * @multiline
	 */
	public $additional_text;

}
