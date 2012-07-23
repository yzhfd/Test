$(function () {
	$('#furnitureDetail_add').click(function(e){
		var holder = $('#HotContainer_furnitureDetailHots');
		var prototype = holder.attr('data-prototype');
		var index = holder.children().length;
		var newForm = prototype.replace(/\$\$name\$\$/g, index);
		holder.append(newForm);
		
		$('#HotContainer_furnitureDetailHots_' + index + '_type').val(1);
	});
});