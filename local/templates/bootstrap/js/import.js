$(document).ready(function() {

	// Импорт кампании
	$('#import_start').click(function() {
		var client = $(this).data('client');
		var id = $(this).data('id');
		$.ajax({
			url: '/api/v1/import/campaign',
			type: 'POST',
			dataType: 'json',
			contentType: 'application/json; charset=utf-8',
			data: '{"client":' + client + ',"id":' + id + '}',
			complete: function(response) {
				console.log(response);
			}
		});
	})
});