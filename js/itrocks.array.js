
if (!Array.prototype.withoutIndex) {
	Array.prototype.withoutIndex = function(index, strict)
	{
		if (strict === undefined) {
			strict = false;
		}
		return this.filter(function(v, i) {
			// noinspection EqualityComparisonWithCoercionJS strict
			return strict ? (i !== index) : (i != index);
		});
	}
}

if (!Array.prototype.withoutValue) {
	Array.prototype.withoutValue = function(value, strict)
	{
		if (strict === undefined) {
			strict = false;
		}
		return this.filter(function(v) {
			// noinspection EqualityComparisonWithCoercionJS strict
			return strict ? (v !== value) : (v != value);
		});
	}
}
