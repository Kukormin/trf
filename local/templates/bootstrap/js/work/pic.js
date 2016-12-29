if (siteOptions.adPage || siteOptions.templPage) {
	var Pic = {
		selectedImg: false,
		init: function () {
			this.imageGroup = $('.image-group');
			this.form = this.imageGroup.closest('form');
			this.image = this.imageGroup.find('.img-polaroid img');
			this.picInput = this.imageGroup.find('input[name=picture]');
			this.loadedModal = this.imageGroup.find('#loaded_modal');
			this.loadedModalBtn = this.loadedModal.find('.btn-primary');
			this.urlBlock = this.imageGroup.find('.input-append');
			this.urlBlockInput = this.urlBlock.find('input');
			this.tmp = this.imageGroup.find('#pic');
			
			this.tmp.on('change', this.newPicture);
			this.imageGroup.find('#loaded_pictures').click(this.showLoadedModal);
			this.imageGroup.find('#load_url').click(this.showUrlInput);
			this.loadedModal.on('click', '.img-polaroid', this.clickPicture);
			this.loadedModalBtn.click(this.selectPicture);
			this.urlBlock.find('.btn').click(this.loadPictureUrl);
			this.urlBlockInput.on('keypress', this.urlBlockInputPress);
		},
		preview: function() {
			if (siteOptions.adPage)
				Ad.preview(true);
			if (siteOptions.templPage)
				Templ.preview(true);
		},
		toggle: function(show) {
			if (show)
				Pic.imageGroup.slideDown();
			else
				Pic.imageGroup.slideUp();
		},
		newPicture: function() {
			var val = Pic.tmp.val();
			if (!val)
				return;

			CMN.ajax('upload_pic', {
				form: Pic.form,
				strategy: 1,
				file: true
			}, function(data) {
				if (data.file && data.id) {
					Pic.image.attr('src', data.file.src);
					Pic.picInput.val(data.id);
				}
				else {
					Pic.image.attr('src', '/i/no-pic.png');
					Pic.picInput.val(0);
				}
				Pic.preview();
			});
		},
		showLoadedModal: function() {
			CMN.ajax('loaded_pictures', {
				form: Pic.form,
				strategy: 1,
				quick: true
			}, function(data) {
				Pic.loadedModal.find('.modal-body').html(data);
			});
			Pic.selectedImg = false;
			Pic.loadedModalBtn.prop('disabled', true);
			Pic.loadedModal.modal('show');
		},
		clickPicture: function() {
			var div = $(this);
			div.addClass('active');
			div.siblings('.active').removeClass('active');
			Pic.selectedImg = div;
			Pic.loadedModalBtn.prop('disabled', false);
		},
		selectPicture: function(e) {
			e.stopPropagation();

			Pic.loadedModal.modal('hide');
			if (Pic.selectedImg)
			{
				var id = Pic.selectedImg.data('id');
				var src = Pic.selectedImg.children('img').attr('src');
				Pic.image.attr('src', src);
				Pic.picInput.val(id);
				Pic.preview();
			}

			return false;
		},
		showUrlInput: function() {
			Pic.urlBlock.slideDown();
			Pic.urlBlockInput.focus();
		},
		loadPictureUrl: function() {
			CMN.ajax('url_pic', {
				form: Pic.form,
				strategy: 1
			}, function(data) {
				if (data.file && data.id) {
					Pic.image.attr('src', data.file.src);
					Pic.picInput.val(data.id);
					Pic.urlBlock.slideUp();
				}
				else {
					Pic.image.attr('src', '/i/no-pic.png');
					Pic.picInput.val(0);
				}
				Pic.preview();
			});
		},
		urlBlockInputPress: function(e) {
			if (e.which == 13) {
				Pic.loadPictureUrl();
				return false;
			}
		}
	};
}
