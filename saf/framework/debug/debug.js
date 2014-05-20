
/**
 * Call formElementsNames(your_form) to list all form element names
 * - You can use it without the form parameter : the first form in page will be taken
 * - You can use it with an integer as form parameter : the matching Xst form in page will be taken
 *
 * @example formElementsNames()
 * @example formElementsNames($('form').get(0)
 * @example formElementsNames(0)
 * @param form HTMLElement|integer
 * @returns String[]
 */
window.formElementsNames = function(form)
{
	if (form === undefined) {
		form = 0;
	}
	if (form === parseInt(form)) {
		form = $('form').get(form);
	}
	var elements = [];
	for (var key in form.elements) if (form.elements.hasOwnProperty(key)) {
		var element = form.elements[key];
		if (element.name) {
			elements.push(element.name);
		}
	}
	return elements;
};
