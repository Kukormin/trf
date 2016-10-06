$(document).ready(function() {
	$('#steps a').click(function (e) {
		e.preventDefault();
		if (!$(this).parent().hasClass('disabled'))
			$(this).tab('show');
	});

	$('form').submit(function () {
		return false;
	});

	var alerts = $('.alerts');
	var metricAlerts = $('.metric_alerts');
	$('#steps li#step70 a').on('shown', function (e) {
		parseMetric();
	});
	if ($('li#step70').hasClass('active'))
		parseMetric();

	var ymlAlerts = $('.yml_alerts');
	$('#steps li#step80 a').on('shown', function (e) {
		parseYml();
	});
	if ($('li#step80').hasClass('active'))
		parseYml();

	$('.new-project-extended').click(function() {
		var form = $(this).closest('form');
		if (form.hasClass('process'))
			return false;

		form.addClass('process');
		$.ajax({
			type: "POST",
			url: '/ajax/new_project.php',
			data: form.serialize(),
			error: function() {
				// TODO: формулировку
				showErrorBox('При сохранении данных возникла ошибка.', alerts);
			},
			success: function(data) {
				if (data.new_project == 1) {
					parseSite();
				}
				if (data.errors) {
					for (var i in data.errors) {
						showErrorBox(data.errors[i], alerts);
					}
				}
				else {
					if (data.redirect)
						location.href = data.redirect;
				}
			},
			complete: function() {
				form.removeClass('process');
				form.find('.hidden').removeClass('hidden');
				form.find('input[name=step]').val(15);
			}
		});
		return false;
	});

	$('button[type=submit]').click(function () {
		var form = $(this).closest('form');
		if (form.hasClass('process'))
			return false;

		form.addClass('process');
		$.ajax({
			type: "POST",
			url: '/ajax/new_project.php',
			data: form.serialize(),
			error: function() {
				// TODO: формулировку
				showErrorBox('При сохранении данных возникла ошибка.', alerts);
			},
			success: function(data) {
				if (data.new_project == 1) {
					parseSite();
				}
				if (data.errors) {
					for (var i in data.errors) {
						showErrorBox(data.errors[i], alerts);
					}
				}
				else {
					if (data.redirect)
						location.href = data.redirect;

					var step = data.step;
					var li = $('#steps li#step' + step);
					li.children('a').tab('show');
					li.removeClass('disabled');
				}
			},
			complete: function() {
				form.removeClass('process');
			}
		});
		return false;
	});

	function showErrorBox(message, div) {
		div.append('<div class="alert alert-error"><button class="close" data-dismiss="alert" type="button">×</button>' + message + '</div>');
	}

	function parseSite() {
		var ind = $('.parse_site');
		if (ind.hasClass('process'))
			return false;

		ind.addClass('process');
		$.ajax({
			type: "GET",
			url: '/ajax/parse_site.php',
			error: function() {

			},
			success: function(data) {
				if (data.error == 'load_error')
				{
					showErrorBox('Ошибка загрузки сайта', alerts);
					return false;
				}

				var input1 = $('input#phone_prefix');
				var input2 = $('input#phone_code');
				var input3 = $('input#phone_number');
				if (!input1.val() && data.PHONE && data.PHONE.prefix) {
					input1.val(data.PHONE.prefix);
				}
				if (!input2.val() && data.PHONE && data.PHONE.code) {
					input2.val(data.PHONE.code);
				}
				if (!input3.val() && data.PHONE && data.PHONE.number) {
					input3.val(data.PHONE.number);
				}

				input1 = $('input#estore1');
				input2 = $('input#estore2');
				if (!input1.prop('checked') && !input2.prop('checked')) {
					if (data.ESTORE == 1)
						input1.prop('checked', 'checked');
					else
						input2.prop('checked', 'checked');
				}

				input1 = $('input#name');
				input2 = $('input#email');
				input3 = $('input#company');
				if (!input1.val() && data.NAME) {
					input1.val(data.NAME);
				}
				if (!input2.val() && data.EMAIL) {
					input2.val(data.EMAIL);
				}
				if (!input3.val() && data.NAME) {
					input3.val(data.NAME);
				}
			},
			complete: function() {
				ind.removeClass('process');
			}
		});
	}

	function parseMetric() {
		var ind = $('.metric_indicator');
		if (ind.hasClass('process'))
			return false;

		if (!$('#yandex_ok').hasClass('hidden'))
			return false;

		ind.addClass('process');
		$('.metric_cont').addClass('hidden');
		$.ajax({
			type: "GET",
			url: '/ajax/parse_metric.php',
			error: function() {
				// TODO: формулировку
				showErrorBox('Не удалось выяснить, установлены ли счетчики на сайте', metricAlerts);
			},
			success: function(data) {
				if (data.yandex) {
					$('#yandex_ok').removeClass('hidden').find('.login').text(data.YANDEX_CLIENT);
				}
				else {
					$('#yandex_empty').removeClass('hidden');
				}

				if (data.google) {
					$('#google_ok').removeClass('hidden').find('.login').text(data.GOOGLE_CLIENT);
				}
				else {
					$('#google_empty').removeClass('hidden');
				}

			},
			complete: function() {
				ind.removeClass('process');
			}
		});
	}

	function parseYml() {
		var cont = $('.catalog');
		if (!cont.hasClass('need_ajax'))
			return false;

		var ind = $('.yml_indicator');
		if (ind.hasClass('process'))
			return false;

		ind.addClass('process');
		$.ajax({
			type: "GET",
			url: '/ajax/parse_yml.php',
			error: function() {
				// TODO: формулировку
				showErrorBox('Ну далось загрузить yml файл', metricAlerts);
			},
			success: function(data) {
				cont.html(data);
			},
			complete: function() {
				ind.removeClass('process');
			}
		});
	}

});