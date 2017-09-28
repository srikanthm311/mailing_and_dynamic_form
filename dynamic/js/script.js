$(document).ready(function(){
	$.validator.addMethod("regx", function(value, element, regexpr) {          
	
		return regexpr.test(value);
	
		}, ".");
	if($('#add_products').length > 0){
	$("#add_products").validate({
		rules: 
		{
			"data[0][name]":
			{
				required: true,
				regx: /^[a-z A-Z]*$/,			
			},
			"data[0][brand]": {
				required: true,
			},
			"data[0][color]": {
				required: true,
			},
			"data[0][price]": {
				required: true,
			},
			"data[0][category]": {
				required: true,
			},
			"data[0][quantity]": {
				required: true,
			},
		},
		messages: 
		{
			"data[0][name]": {
				required: 'Enter product name',
			},
			"data[0][brand]": {
				required: 'Enter product brand',
			},
			"data[0][color]": {
				required: 'Enter product color',
			},
			"data[0][price]": {
				required: 'Enter product price',
			},
			"data[0][category]": {
				required: 'Select product category'
			},
			"data[0][quantity]": {
				required: 'Enter quantity',
			},
				
		}
 });
}
	
	$('.addclone').click(function(){
		var id_num = $(".del_clone:last").attr("id");
		
		var sfx = id_num.substr(13);
		var i = parseInt(sfx);
		i = i +1;
		
		var clonned = $('.clonned_tr:first').clone(true);
		clonned.find('input:text').val('');
		clonned.find('label[class=error]').remove();

		clonned.fadeIn('slow').insertAfter('.clonned_tr:last');	
		
		clonned.find('.increase_index').each(function()
		{
			var result = $(this).attr('id', $(this).attr('id').replace(/\d+/, i)).index();
		});
		
		
		clonned.find('#delete_clone_'+i).show();
		
		$(".del_clone").click(function()
		{
			$(this).closest('tr').remove();
			
		});
		
		clonned.find('.increase_index').each(function()
		{
			var result = $(this).attr('name', $(this).attr('name').replace(/\d+/, i)).index();
			
		});
			
		
		$('.input_validate').each(function(){
		var fieldName = $(this).attr('name');
		var message_str = fieldName.substr(8);
		 message_str = message_str.slice(0,-1);
		$("[name='" + fieldName + "']")
		.rules('add', 
			{
				required: true,
			messages: {
				required: "Enter Product "+message_str,
			}
		});
	
		});
		
				
	});
});
	
		/*clonned.find('.input_validate').each(function(){
			alert($(this).attr('name'))
			$($(this).attr('name')).rules('add',
			{
				required : true,
				message : {
					required : 'Enter name',
					}
			});
			
		});*/
		/*clonned.find('.input_validate').each(function(){
			$($(this).attr('name')).validate({
				rules : {
					'data[1][name]':{
						required : true,	
						},
					}
				});*/