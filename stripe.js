$(function() {
	$('.vbx-stripe :checkbox').click(function() {
		$('.vbx-stripe form p').eq(5).slideToggle();
	});
	if(!$('.vbx-stripe :checked').length)
		$('.vbx-stripe form p').eq(5).hide();
});
