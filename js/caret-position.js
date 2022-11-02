function caretPosition()
{
	if (window.getSelection && window.getSelection().getRangeAt) {
		const selected = window.getSelection()
		const range    = selected.getRangeAt(0)
		let   count    = 0
		const nodes    = selected.anchorNode.parentNode.childNodes
		for (let i = 0; i < nodes.length; i++) {
			if (nodes[i] === selected.anchorNode) {
				break
			}
			if (nodes[i].outerHTML) {
				count += nodes[i].outerHTML.length
			}
			else if (nodes[i].nodeType === 3) {
				count += nodes[i].textContent.length
			}
		}
		return range.startOffset + count
	}
	return -1
}
