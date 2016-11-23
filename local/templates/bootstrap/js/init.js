var siteOptions = {
	titleParts: [],
	titleSep: ' - ',
	indexPage: false,
	projectPage: false,
	categoryPage: false,
	keygroupFilters: false,
	linksetPage: false,
	vcardPage: false,
	templPage: false,
	keygroupPage: false,
	adPage: false
};
siteOptions.prototype = {
	endless: false,
	redirect: '',
	alerts: [],
	EX: false,
	AUTO: false,
	MANUAL: false,
	geoObjects: false,
	geometry: false,
	getCoordinates: false,
	setCoordinates: false,
	Placemark: false,
	properties: false,
	GeocoderMetaData: false
};
var ymaps = false;
var mapOptions = {X:55.76,Y:37.64,X1:55.5,Y1:36.5,X2:56,Y2:38.58};
