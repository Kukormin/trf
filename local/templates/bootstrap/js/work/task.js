var Task = {
	timerId: 0,
	init: function () {
	},
	check: function() {
		CMN.ajax('check_task', {
			strategy: 1
		}, function (data) {
			if (data.off)
				Task.off();
		});
	},
	on: function() {
		Task.timerId = setInterval(Task.check, 30000);
	},
	off: function() {
		clearInterval(Task.timerId);
	}
};
