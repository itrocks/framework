
//-------------------------------------------------------------------------------------- lLastParse
String.prototype.lLastParse = function (sep, cnt, complete_if_not)
{
	var str = this;
	if (cnt == undefined) cnt = 1;
	if (complete_if_not == undefined) complete_if_not = true;
	if (cnt > 1) {
		str = this.lLastParse(sep, cnt - 1);
	}
	var i = str.lastIndexOf(sep);
	if (i == -1) {
		return complete_if_not ? str : '';
	} else {
		return str.substr(0, i);
	}
};

//------------------------------------------------------------------------------------------ lParse
String.prototype.lParse = function (sep, cnt, complete_if_not)
{
	if (cnt == undefined) cnt = 1;
	if (complete_if_not == undefined) complete_if_not = true;
	var i = -1;
	while (cnt --) {
		i = this.indexOf(sep, i + 1);
	}
	if (i == -1) {
		return complete_if_not ? this : '';
	}
	else {
		return this.substr(0, i);
	}
};

//-------------------------------------------------------------------------------------- rLastParse
String.prototype.rLastParse = function (sep, cnt, complete_if_not)
{
	var str = this;
	if (cnt == undefined) cnt = 1;
	if (complete_if_not == undefined) complete_if_not = false;
	if (cnt > 1) {
		str = this.rLastParse(sep, cnt - 1);
	}
	var i = str.lastIndexOf(sep);
	if (i == -1) {
		return complete_if_not ? str : '';
	} else {
		return str.substr(i + sep.length);
	}
};

//------------------------------------------------------------------------------------------ rParse
String.prototype.rParse = function (sep, cnt, complete_if_not)
{
	if (cnt == undefined) cnt = 1;
	if (complete_if_not == undefined) complete_if_not = false;
	var i = -1;
	while (cnt --) {
		i = this.indexOf(sep, i + 1);
	}
	if (i == -1) {
		return complete_if_not ? this : '';
	}
	else {
		return this.substr(i + sep.length);
	}
};

//-------------------------------------------------------------------------------------------- repl
String.prototype.repl = function(from, to)
{
	var replaced = '';
	var start = 0;
	var length = from.length;
	var i = this.indexOf(from, start);
	while (i > -1) {
		if (i > start) {
			replaced += this.substring(start, i);
		}
		replaced += to;
		start = i + length;
		i = this.indexOf(from, start);
	}
	replaced += this.substring(start);
	return replaced;
};
