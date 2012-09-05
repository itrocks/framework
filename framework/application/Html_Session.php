<?php
namespace SAF\Framework;

class Html_Session
{

	//--------------------------------------------------------------------------------- postSessionId
	/**
	 * Send session ID as a POST var
	 * This is done at end of html templates parsing 
	 *
	 * @param AopJoinpoint $joinpoint
	 */
	public static function postSessionId($joinpoint)
	{
		$content = $joinpoint->getReturnedValue();
		// $_POST
		$content = str_replace(
			"</form>",
			"<input type=\"hidden\" name=\"" . session_name() . "\" value=\"" . session_id() . "\"></form>",
			$content
		);
		// $_GET
		$i = 0;
		while (($i = strpos($content, 'href="', $i)) !== false) {
			$i += 6;
			$j = strpos($content, '"', $i);
			$link = substr($content, $i, $j - $i);
			$sep = strpos($link, "?") ? "&" : "?"; 
			$content = substr($content, 0, $j) . $sep
				. session_name() . "=" . session_id()
				. substr($content, $j);
			$i = $j;
		}
		// done
		$joinpoint->setReturnedValue($content);
	}

}

Aop::registerAfter("SAF\\Framework\\Html_Template->parse()", "SAF\\Framework\\Html_Session::postSessionId");
