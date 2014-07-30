$(document).ready(function(){
	//全选
	$('.check-all').click(function(e){
		var checked = this.checked;
		if (checked == true) {
			 $('input[name="id[]"]').attr('checked','true');
		} else {
			 $('input[name="id[]"]').removeAttr('checked');
		}
	})

	//批量删除
	$('.del-btn').click(function(e){
		$('.del-form').submit();
	})
})