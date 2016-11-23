if (siteOptions.keygroupPage) {
	var KeyGroup = {
		init: function () {
			this.form = $('#keygroup_detail');
			this.form.find('input[type=checkbox]').click(this.markClick);
		},
		markClick: function() {
			KeyGroup.saveSettings();
		},
		saveSettings: function () {
			CMN.ajax('keygroup_save', {
				form: KeyGroup.form,
				strategy: 2
			}, false);
		}
	};
}
