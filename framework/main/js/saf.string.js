String.prototype.lLastParse = function (sep, cnt, complete_if_not)
{
	var str = this;
	if (cnt == undefined) cnt = 1;
	if (complete_if_not == undefined) complete_if_not = true;
	if (cnt > 1) {
		str = this.lLastParse(sep, cnt - 1);
	}
	i = str.lastIndexOf(sep);
	if (i == -1) {
		return complete_if_not ? str : "";
	} else {
		return str.substr(0, i);
	}
}
