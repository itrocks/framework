$("document").ready(function() {

	$(".edit").click(function() {
		var $this = $(this);
		if ($this.parents("form").length) {
			$this.children(":first").focus();
		}
		else {
			$this.parents(".form").replaceWith(function(){
				$this = $(this);
				return $("<form>", {
					"class": $this.attr("class"),
					"id":    $this.attr("id")
				}).append($this.children());
			});
			var newInput = function(element) {
				return $("<input>", {
					"name": element.attr("id"),
					"value": element.text()
				})
				.css({
					"width": getInputTextWidth(element.text())
				})
				.keyup(function() { $(this).width(getInputTextWidth(this.value)); });
			};
			var input = newInput($this);
			$this.html(input);
			input.focus();
			$(".edit").not("input")
				.html(function() {
					return newInput($(this));
				});
			$(".ifedit").css("display", "inline-block");
		}
	});

});
