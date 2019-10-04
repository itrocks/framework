
//-------------------------------------------------------------------------------------- beginsWith
if (!String.prototype.beginsWith) {
	String.prototype.beginsWith = function(string)
	{
		return this.indexOf(string) === 0;
	}
}

//---------------------------------------------------------------------------------------- contains
if (!String.prototype.contains) {
	String.prototype.contains = function(search)
	{
		return this.indexOf(search) > -1;
	}
}

//---------------------------------------------------------------------------------------- endsWith
if (!String.prototype.endsWith) {
	String.prototype.endsWith = function(search, position)
	{
		var subject = this.toString();
		if (
			(typeof position !== 'number')
			|| !isFinite(position)
			|| (Math.floor(position) !== position)
			|| (position > subject.length)
		) {
			position = subject.length;
		}
		position      -= search.length;
		var last_index = subject.indexOf(search, position);
		return (last_index !== -1) && (last_index === position);
	};
}

//----------------------------------------------------------------------------------------- flexCmp
/**
 * Compare two strings without taking care of trailing spaces, case or accents
 *
 * @param text string
 * @return number|integer -1, 0 or 1
 */
String.prototype.flexCmp = function(text)
{
	var text1 = this.simple();
	var text2 = text.simple();
	return (text1 === text2) ? 0 : ((text1 < text2) ? -1 : 1);
};

//-------------------------------------------------------------------------------------- lLastParse
String.prototype.lLastParse = function(sep, cnt, complete_if_not)
{
	var str = this;
	if (cnt             === undefined) cnt             = 1;
	if (complete_if_not === undefined) complete_if_not = true;
	if (cnt > 1) {
		str = this.lLastParse(sep, cnt - 1);
	}
	var i = str.lastIndexOf(sep);
	if (i === -1) {
		return complete_if_not ? str : '';
	}
	else {
		return str.substr(0, i);
	}
};

//------------------------------------------------------------------------------------------ lParse
String.prototype.lParse = function(sep, cnt, complete_if_not)
{
	if (cnt             === undefined) cnt             = 1;
	if (complete_if_not === undefined) complete_if_not = true;
	var i = -1;
	while (cnt --) {
		i = this.indexOf(sep, i + 1);
	}
	if (i === -1) {
		return complete_if_not ? this : '';
	}
	else {
		return this.substr(0, i);
	}
};

//-------------------------------------------------------------------------------------------- repl
String.prototype.repl = function(from, to)
{
	var replaced = '';
	var start    = 0;
	var length   = from.length;
	var i        = this.indexOf(from, start);
	while (i > -1) {
		if (i > start) {
			replaced += this.substring(start, i);
		}
		replaced += to;
		start     = i + length;
		i         = this.indexOf(from, start);
	}
	replaced += this.substring(start);
	return replaced;
};

//-------------------------------------------------------------------------------------- rLastParse
String.prototype.rLastParse = function(sep, cnt, complete_if_not)
{
	var str = this;
	if (cnt             === undefined) cnt             = 1;
	if (complete_if_not === undefined) complete_if_not = false;
	if (cnt > 1) {
		str = this.rLastParse(sep, cnt - 1);
	}
	var i = str.lastIndexOf(sep);
	if (i === -1) {
		return complete_if_not ? str : '';
	}
	else {
		return str.substr(i + sep.length);
	}
};

//------------------------------------------------------------------------------------------ rParse
String.prototype.rParse = function(sep, cnt, complete_if_not)
{
	if (cnt             === undefined) cnt             = 1;
	if (complete_if_not === undefined) complete_if_not = false;
	var i = -1;
	while (cnt --) {
		i = this.indexOf(sep, i + 1);
	}
	if (i === -1) {
		return complete_if_not ? this : '';
	}
	else {
		return this.substr(i + sep.length);
	}
};

//------------------------------------------------------------------------------------------ simple
/**
 * Calculates a simplified version of the text : trim, lowercase,
 * replace accents with non-accentuated characters
 *
 * @return string
 */
String.prototype.simple = function()
{
	return this.trim().toLowerCase().withoutAccents();
};

//-------------------------------------------------------------------------------------- startsWith
if (!String.prototype.startsWith) {
	String.prototype.startsWith = function(search, position)
	{
		position = position || 0;
		return this.substr(position, search.length) === search;
	};
}

//----------------------------------------------------------------------------------------- ucfirst
String.prototype.ucfirst = function()
{
	return this.charAt(0).toUpperCase() + this.slice(1);
};

//---------------------------------------------------------------------------------- withoutAccents
/**
 * Replace accents by the closest char
 *
 * @return string
 */
String.prototype.withoutAccents = function()
{
	var str_simplify = {
		'À': 'A', 'Á': 'A', 'Â': 'A', 'Ã': 'A', 'Ä': 'A', 'Å': 'A',
		'Ç': 'C',
		'È': 'E', 'É': 'E', 'Ê': 'E', 'Ë': 'E',
		'Ì': 'I', 'Í': 'I', 'Î': 'I', 'Ï': 'I',
		'Ò': 'O', 'Ó': 'O', 'Ô': 'O', 'Õ': 'O', 'Ö': 'O',
		'Ù': 'U', 'Ú': 'U', 'Û': 'U', 'Ü': 'U',
		'Ý': 'Y', 'Ÿ': 'Y',
		'à': 'a', 'á': 'a', 'â': 'a', 'ã': 'a', 'ä': 'a', 'å': 'a',
		'ç': 'c',
		'è': 'e', 'é': 'e', 'ê': 'e', 'ë': 'e',
		'ì': 'i', 'í': 'i', 'î': 'i', 'ï': 'i',
		'ð': 'o', 'ò': 'o', 'ó': 'o', 'ô': 'o', 'õ': 'o', 'ö': 'o',
		'ù': 'u', 'ú': 'u', 'û': 'u', 'ü': 'u',
		'ý': 'y', 'ÿ': 'y',
		'&': 'and'
	};
	var length = this.length;
	var result = '';
	for (var i = 0; i < length; i++) {
		var char = str_simplify[this[i]];
		result += (char === undefined) ? this[i] : char;
	}
	return result;
};
