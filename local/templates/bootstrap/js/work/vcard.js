if (siteOptions.vcardPage) {
	var Vcard = {
		name: '',
		nameTimerId: 0,
		address: '',
		geoTimerId: 0,
		fieldsError: false,
		nameError: false,
		checked: false,
		init: function () {
			this.form = $('#vcard_detail');
			this.btnSave = this.form.find('.btn-primary');
			this.btnCancel = this.form.find('.cancel');
			this.nameInput = this.form.find('#name');
			this.nameControlGroup = this.nameInput.closest('.control-group');
			this.nameHelp = this.nameControlGroup.find('.help-inline');
			this.addressCountry = this.form.find('input[name="data[Country]"]');
			this.addressCity = this.form.find('input[name="data[City]"]');
			this.addressStreet = this.form.find('input[name="data[Street]"]');
			this.addressHouse = this.form.find('input[name="data[House]"]');
			this.addressBuilding = this.form.find('input[name="data[Building]"]');
			this.phoneCountryCode = this.form.find('input[name="data[Phone][CountryCode]"]');
			this.phoneCityCode = this.form.find('input[name="data[Phone][CityCode]"]');
			this.phonePhoneNumber = this.form.find('input[name="data[Phone][PhoneNumber]"]');
			this.phoneExtension = this.form.find('input[name="data[Phone][Extension]"]');
			this.yandexPhone = this.form.find('#yandex-phone');
			this.yandexCity = this.form.find('#yandex-city');
			this.yandexRegime = this.form.find('#yandex-regime');
			this.mapLoader = this.form.find('.map_info .loader');
			this.mapInfo = this.form.find('.map_info .help-inline');
			this.googleAddress = this.form.find('#google_preview');
			this.googlePhone = this.form.find('#google-phone');
			this.googleRegime = this.form.find('#google-regime');

			this.initCheck();

			this.nameInput.on('input', this.checkName);
			this.form.on('input', 'input.required', this.checkFields);
			this.btnSave.click(this.saveSettings);
			this.btnCancel.click(CMN.historyBack);
			this.form.on('input', '.phone', this.phoneChange);
			this.form.on('input', '.address', this.addressChange);
			this.form.find('#vcard_preview').click(this.preview);
		},
		initCheck: function() {
			this.address = Vcard.getAddress();
			this.name = this.nameInput.val();
			if (!this.name)
				Vcard.nameError = true;
			Vcard.checkFields();
		},
		btnDisabled: function() {
			Vcard.btnSave.prop('disabled', Vcard.fieldsError || Vcard.nameError);
		},
		checkName: function () {
			var newName = Vcard.nameInput.val();
			if (Vcard.name == newName)
				return;

			Vcard.name = newName;
			if (Vcard.nameTimerId)
				clearTimeout(Vcard.nameTimerId);
			if (Vcard.name) {
				Vcard.nameControlGroup.removeClass('error');
				Vcard.nameHelp.text('');
				Vcard.nameError = false;
				Vcard.nameTimerId = setTimeout(Vcard.saveSettingsAjax, 500);
			}
			else {
				Vcard.nameControlGroup.addClass('error');
				Vcard.nameHelp.text('Введите название визитки');
				Vcard.nameError = true;
			}
			Vcard.btnDisabled();
		},
		checkFields: function () {
			var firstInput = false;
			Vcard.fieldsError = false;
			if (Vcard.checked) {
				Vcard.form.find('.required').each(function () {
					var input = $(this);
					var group = input.closest('.control-group');
					var help = group.find('.help-inline');
					if (!input.val()) {
						group.addClass('error');
						help.text('Обязательное поле');
						Vcard.fieldsError = true;
						if (!firstInput)
							firstInput = input;
					}
					else {
						group.removeClass('error');
						help.text('');
					}
				});
			}
			Vcard.btnDisabled();
			return firstInput;
		},
		saveSettingsAjax: function (save) {
			var addParams = '';
			if (!save)
				addParams = 'only_check=Y';
			CMN.ajax('vcard_save', {
				form: Vcard.form,
				post: addParams,
				strategy: 2,
				overlay: save
			}, function (data) {
				if (data.EX) {
					Vcard.nameControlGroup.addClass('error');
					Vcard.nameHelp.text('Визитка с таким именем уже существует');
					Vcard.nameError = true;
				}
				else {
					Vcard.nameControlGroup.removeClass('error');
					Vcard.nameHelp.text('');
					Vcard.nameError = false;
				}
				Vcard.btnDisabled();
			});
		},
		saveSettings: function () {
			if (!Vcard.checked) {
				Vcard.checked = true;
				var firstInput = Vcard.checkFields();
				if (firstInput)
					firstInput.focus();
			}
			if (Vcard.fieldsError)
				return false;

			if (Vcard.nameTimerId)
				clearTimeout(Vcard.nameTimerId);

			Vcard.saveSettingsAjax(true);

			return false;
		},
		phoneChange: function() {
			var phone = Vcard.phoneCountryCode.val() + ' (' + Vcard.phoneCityCode.val() + ') ' +
				Vcard.phonePhoneNumber.val();
			var ext = Vcard.phoneExtension.val();
			if (ext)
				phone += ' доб.' + ext;
			Vcard.yandexPhone.text(phone);
			Vcard.googlePhone.text(phone);
		},
		getAddress: function() {
			var country = Vcard.addressCountry.val();
			if (!country)
				return '';

			var city = Vcard.addressCity.val();
			if (!city)
				return '';

			var q = country + ', ' + city + ', ' + Vcard.addressStreet.val();
			var house = Vcard.addressHouse.val();
			if (house)
				q += ', д.' + house;
			var build = Vcard.addressBuilding.val();
			if (build)
				q += ', стр.' + build;

			return q;
		},
		getGoogleAddress: function() {
			var city = Vcard.addressCity.val();
			var street = Vcard.addressStreet.val();
			var house = Vcard.addressHouse.val();
			var building = Vcard.addressBuilding.val();

			var q = street;
			if (house) {
				if (q)
					q += ', ';
				q += 'д.' + house;
			}
			if (building) {
				if (q)
					q += ', ';
				q += 'к.' + building;
			}
			if (city) {
				if (q)
					q += ', ';
				q += city;
			}

			if (q)
				q = '<span class="pos"></span>' + q;

			return q;
		},
		addressChange: function() {
			var googleAddress = Vcard.getGoogleAddress();
			Vcard.googleAddress.html(googleAddress);

			var newAddress = Vcard.getAddress();
			if (Vcard.address == newAddress)
				return;

			if ($(this).is('#City'))
				Vcard.yandexCity.text($(this).val());

			Vcard.address = newAddress;
			if (Vcard.geoTimerId)
				clearTimeout(Vcard.geoTimerId);
			if (Vcard.address) {
				Vcard.geoTimerId = setTimeout(Vcard.getXYbyAddress, 500);
			}
		},
		getXYbyAddress: function() {
			if (!PointMap.map)
				return;

			Vcard.mapLoader.addClass('inp');
			Vcard.mapInfo.text('');
			var geo = ymaps.geocode(Vcard.address, {
				results: 1
			});
			geo.then(function(res) {
				var go = res.geoObjects.get(0);
				var meta = go.properties.get('metaDataProperty');
				var kind = meta.GeocoderMetaData.kind;
				var t = 'Ошибка определения. Вы можете установить положение метки вручную.';
				if (kind == 'house')
					t = 'Определено с точностью до дома. Вы можете скорректировать положение метки.';
				else if (kind == 'street')
					t = 'Определено с точностью до улицы. Вы можете скорректировать положение метки.';
				else if (kind == 'locality')
					t = 'Определено с точностью до города. Вы можете скорректировать положение метки.';
				var coords = go.geometry.getCoordinates();
				var bounds = go.properties.get('boundedBy');
				if (bounds && coords) {
					PointMap.map.setBounds(bounds);
					PointMap.pm.geometry.setCoordinates(coords);
					PointMap.setXY(coords[0], coords[1]);
				}
				Vcard.mapLoader.removeClass('inp');
				Vcard.mapInfo.text(t);
			});
		},
		preview: function() {
			var id = $(this).data('id');
			if (!id) {
				var href = '/vcard/?' + Vcard.form.serialize();
				$(this).attr('href', href);
			}
		}
	};
}
