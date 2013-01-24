<?php
namespace SAF\Framework;

require_once "vendor/textile/classTextile.php";

class Textile extends \Textile
{

	//------------------------------------------------------------------------------------------ span
	function span($text)
	{
		// BA $qtags = array('\*\*','\*','\?\?','-','__','_','%','\+','~','\^');
		$qtags = array('\*\*','\*','\?\?','-','\/\/','\/','%','_','~','\^');
		$pnct = ".,\"'?!;:";
		foreach($qtags as $f) {
			$text = preg_replace_callback("/
				(?:^|(?<=[\s>$pnct])|([{[]))
				($f)(?!$f)
				({$this->c})
				(?::(\S+))?
				([^\s$f]+|\S[^$f\n]*[^\s$f\n])
				([$pnct]*)
				$f
				(?:$|([\]}])|(?=[[:punct:]]{1,2}|\s))
				/x", array(&$this, "fSpan"), $text
			);
		}
		return $text;
	}

	//----------------------------------------------------------------------------------------- fSpan
	function fSpan($m)
	{
		$qtags = array(
			'*'  => 'strong',
			'**' => 'b',
			'??' => 'cite',
			'/'  => 'em',   // BA '_'
			'//' => 'i',    // BA '//'
			'-'  => 'del',
			'%'  => 'span',
			'_'  => 'ins',  // BA '+'
			'~'  => 'sub',
			'^'  => 'sup',
		);
		list(, , $tag, $atts, $cite, $content, $end) = $m;
		$tag = $qtags[$tag];
		$atts = $this->pba($atts);
		$atts .= ($cite != '') ? 'cite="' . $cite . '"' : '';
		$out = "<$tag$atts>$content$end</$tag>";
		return $out;
	}

}
