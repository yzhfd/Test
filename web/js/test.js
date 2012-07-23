$(function () {
	$('#furnitureDetail_add').click(function(e){
		var holder = $('#form_hotContainer_furnitureDetailHots');
		var prototype = holder.attr('data-prototype');
		var index = holder.children().length;
		var newForm = prototype.replace(/\$\$name\$\$/g, index);
		holder.append(newForm);
		
		$('#form_hotContainer_furnitureDetailHots_' + index + '_type').val(1);
	});
});