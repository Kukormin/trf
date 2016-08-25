$(document).ready(function() {

	// Синхронизация
	$('.client_sync').click(function() {
		var btn = $(this);
		var results = btn.parent().next('.results');
		results.html();
		btn.button('loading');
		var client = btn.data('client');
		var id = 0;
		if (btn.data('id'))
			id = btn.data('id');
		$.ajax({
			url: '/api/v1/import/campaign',
			type: 'POST',
			dataType: 'json',
			contentType: 'application/json; charset=utf-8',
			data: '{"client":' + client + ',"id":' + id + '}',
			complete: function(response) {
				btn.button('reset');
				var encoded = jQuery.parseJSON(response.responseText);
				results.html(encoded.html);
			}
		});
	})
});