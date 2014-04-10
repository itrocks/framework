$(document).ready(function() {

	document.mouse = { x: 0, y: 0 };
	$(document).mousemove(function(event) {
		document.mouse.x = event.pageX;
		document.mouse.y = event.pageY;
	});

});
