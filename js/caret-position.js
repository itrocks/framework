function caretPosition()
{
	if (window.getSelection && window.getSelection().getRangeAt) {
		var selected = window.getSelection();
		var range    = selected.getRangeAt(0);
		var count    = 0;
		var nodes    = selected.anchorNode.parentNode.childNodes;
		for (var i = 0; i < nodes.length; i++) {
			if (nodes[i] === selected.anchorNode) {
				break;
			}
			if (nodes[i].outerHTML) {
				count += nodes[i].outerHTML.length;
			}
			else if (nodes[i].nodeType === 3) {
				count += nodes[i].textContent.length;
			}
		}
		return range.startOffset + count;
	}
	return -1;
}
