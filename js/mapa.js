var map;
var idInfoBoxAberto;
var infoBox = [];
var markers = [];

function initialize() {	
	var latlng = new google.maps.LatLng(-14.8800397, -54.05878999999999);
	
	var options = {
		zoom: 4,
		center: latlng,
		mapTypeId: google.maps.MapTypeId.TERRAIN
	};

	map = new google.maps.Map(document.getElementById("mapa"), options);
}

initialize();

function abrirInfoBox(id, marker) {
	if (typeof(idInfoBoxAberto) == 'number' && typeof(infoBox[idInfoBoxAberto]) == 'object') {
		infoBox[idInfoBoxAberto].close();
	}

	infoBox[id].open(map, marker);
	idInfoBoxAberto = id;
}

function carregarPontos(){

	var latlngbounds = new google.maps.LatLngBounds();

	$.getJSON('php/pontos.json', function(pontos) {
		$.each(pontos, function(index, ponto){

			var latitude = ponto.Latitude;
			var longitude = ponto.Longitude;
			
			var marker = new google.maps.Marker({
				title: ponto.Title,
				icon: 'img/marcador.png',
				position: new google.maps.LatLng(latitude,longitude),
				map: map,
				center:({lat:-14.8800397, lng:-54.05878999999999})
			}); 

			var myOptions = {
				content: "<p>" + ponto.Descricao + "</p>",
				pixelOffset: new google.maps.Size(-150, 0)
			};

			infoBox[ponto.Id] = new InfoBox(myOptions);
			infoBox[ponto.Id].marker = marker;

			infoBox[ponto.Id].listener = google.maps.event.addListener(marker, 'click', function (e) {
				abrirInfoBox(ponto.Id, marker);
			});

			markers.push(marker);
			latlngbounds.extend(marker.position);
			var markerCluster = new MarkerClusterer(map, markers);

		}); // Fecha Each
		

	}); //Fecha getJson
	map.fitBounds(latlngbounds);

	
} //Fecha Function

carregarPontos();