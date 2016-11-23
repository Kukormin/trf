if (siteOptions.vcardPage) {
	var PointMap = {
		init: function () {
			this.inputX = $('input[name="data[PointOnMap][X]"]');
			this.inputY = $('input[name="data[PointOnMap][Y]"]');
			this.inputX1 = $('input[name="data[PointOnMap][X1]"]');
			this.inputY1 = $('input[name="data[PointOnMap][Y1]"]');
			this.inputX2 = $('input[name="data[PointOnMap][X2]"]');
			this.inputY2 = $('input[name="data[PointOnMap][Y2]"]');
			ymaps.ready(PointMap.initMap);
		},
		initMap: function() {
			PointMap.map = new ymaps.Map("vcard_map", {
				bounds: [[mapOptions.X1, mapOptions.Y1], [mapOptions.X2, mapOptions.Y2]],
				controls: ['typeSelector']
			},{
				suppressMapOpenBlock: true
			});
			PointMap.map.controls.add('zoomControl', {
				size: "large",
				position: {
					top: 50,
					left: 10
				}
			});
			PointMap.pm = new ymaps.Placemark([mapOptions.X, mapOptions.Y], {
				iconContent: 'Передвиньте метку'
			}, {
				preset: 'islands#blueStretchyIcon',
				draggable: true
			});
			PointMap.pm.events.add('dragend', function () {
				var coords = PointMap.pm.geometry.getCoordinates();
				PointMap.setXY(coords[0], coords[1]);
			});
			PointMap.map.geoObjects.add(PointMap.pm);
			PointMap.map.events.add('boundschange', function(e) {
				var bonds = e.get('newBounds');
				PointMap.set12(bonds[0][0], bonds[0][1], bonds[1][0], bonds[1][1]);
			});
		},
		setXY: function(x, y) {
			PointMap.inputX.val(x);
			PointMap.inputY.val(y);
		},
		set12: function(x1, y1, x2, y2) {
			PointMap.inputX1.val(x1);
			PointMap.inputY1.val(y1);
			PointMap.inputX2.val(x2);
			PointMap.inputY2.val(y2);
		}
	};
}