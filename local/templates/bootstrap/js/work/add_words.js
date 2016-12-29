if (siteOptions.categoryPage) {
	var AddWords = {
		init: function () {
			this.form = $('#additional_words');
			this.saveBtn = this.form.find('.btn-primary');
			this.saveBtn.click(this.save);
		},
		save: function() {
			CMN.ajax('category_save_additional', {
				form: AddWords.form
			}, false);
			return false;
		}
	};
}