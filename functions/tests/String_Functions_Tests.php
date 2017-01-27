<?php
namespace ITRocks\Framework\Functions\Tests;

use ITRocks\Framework\Tests\Test;

/**
 * Unit tests for the string_functions.php functions library
 */
class String_Functions_Tests extends Test
{

	//----------------------------------------------------------------------- testBase64EncodeUrlSafe
	public function testBase64EncodeUrlSafe()
	{
		$this->assume(
			__METHOD__,
			base64_encode_url_safe('Test encodage avec des +, / et ='),
			'VGVzdCBlbmNvZGFnZSBhdmVjIGRlcyArLCAvIGV0ID0.'
		);
	}

	//-------------------------------------------------------------------------------- testStrReplace
	/**
	 * @return boolean
	 */
	public function testStrReplace()
	{
		$subject = 'This is a text where some things wanna be searched and replaced';
		$replace = [
			'This' => 'These',
			'is'   => 'are',
			' a '  => ' some ',
			'text' => 'texts',
			'ed'   => 'ED'
		];
		$result = 'These are some texts where some things wanna be searchED and replacED';
		return $this->assume(__METHOD__, strReplace($replace, $subject), $result);
	}

}
