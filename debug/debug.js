
/**
 * Call formElementsNames(your_form) to list all form element names
 * - You can use it without the form parameter : the first form in page will be taken
 * - You can use it with an integer as form parameter : the matching Xst form in page will be taken
 *
 * @example formElementsNames()
 * @example formElementsNames($('form').get(0)
 * @example formElementsNames(0)
 * @param form HTMLElement|integer
 * @return String[]
 */
window.formElementsNames = (form) =>
{
	if (form === undefined) {
		form = 0
	}
	if (form === parseInt(form)) {
		form = $('form').get(form)
	}
	const elements = []
	for (const key in form.elements) if (form.elements.hasOwnProperty(key)) {
		const element = form.elements[key]
		if (element.name) {
			elements.push(element.name)
		}
	}
	return elements
}
