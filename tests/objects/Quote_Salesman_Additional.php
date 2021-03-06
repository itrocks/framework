<?php
namespace ITRocks\Framework\Tests\Objects;

/**
 * A salesman with specific data for its link to a quote and with additional specific data
 *
 * The 'link' annotation allows to consider this class as a link class
 *
 * @link Quote_Salesman
 * @store_name test_quotes_salesmen_additional
 */
class Quote_Salesman_Additional extends Quote_Salesman
{

	//------------------------------------------------------------------------------ $additional_text
	/**
	 * @multiline
	 * @var string
	 */
	public $additional_text;

}
