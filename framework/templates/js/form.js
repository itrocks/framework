$("document").ready(function() {

	$(".edit").click(function() {
		var $this = $(this);
		if ($this.hasClass("editing")) {
			$this.children(":first").focus();
		}
		else {
			$this.addClass("editing");
			var newInput = function(element) {
				return $("<input>", {
					"name": element.attr("id"),
					"value": element.text()
				})
				.css({
					"width": getInputTextWidth(element.text())
				})
				.keyup(function() {
					$(this).width(getInputTextWidth(this.value));
				});
			};
			var input = newInput($this);
			$this.html(input);
			input.focus();
			$(".edit").not(".editing").html(function() {
				var $this = $(this);
				$this.addClass("editing");
				return newInput($(this));
			});
			$(".ifedit").css("display", "inline-block");
		}
	});

});
