// オブジェクト定義
var map;						// マップオブジェクト
var markdata;					// 施設・店舗情報

// マップの中心位置設定
var centerLat = base_lat;
var centerLng = base_lon;

var vImageColor = {0: 'gray', 1: 'yellow', 2: 'white', 3: 'white', 4: 'white'};	// マーカーの色

/* 地図の初期化 */
function initialize(prm_lat, prm_lng) {

	/* 地図のオプション設定 */
	var myOptions={
		/*初期のズーム レベル */
		zoom: map_max_size,
		/* 地図の中心点 */
		center: new google.maps.LatLng(prm_lat, prm_lng),
		/* デフォルトの UI */
		disableDefaultUI: true,
/*
		panControl: false,
		zoomControl: true,
		mapTypeControl: false,
		scaleControl: false,
		streetViewControl: false,
		overviewMapControl: false,
*/
		/* 地図タイプ */
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};

	/* 地図オブジェクト */
	map = new google.maps.Map(document.getElementById("map_detail"), myOptions);

	/* 店舗の座標設定 */
	var place_latlng= new google.maps.LatLng(prm_lat, prm_lng);

    /* マーカーアイコン */
    var iconImg = new google.maps.MarkerImage(
            './img/marker/marker_white.png'
    );

    /* マーカーオブジェクト */
    var markerIns = new google.maps.Marker({
            position: place_latlng,
            map: map,
            icon: iconImg,
            title: '位置登録'
    });

    // 中心位置移動時の処理
    google.maps.event.addListener(map, 'center_changed', function(){
        var pos = map.getCenter();
        markerIns.setPosition(pos);
        markerIns.setTitle('map center: ' + pos);
    });
 
	// 郵便番号設定
	setAddress();
 
        // 曜日プルダウン編集
        var weekSelect = document.getElementById('off_week_code');
        if(weekSelect.length < 1) {
            weekArray.forEach(function(value, key) {
                var weekOption = document.createElement('option');
                weekOption.setAttribute('value', key);
                weekOption.innerHTML = value;
                weekSelect.appendChild(weekOption);
            });
        }
        // 入力項目クリア
        insFormClear();
}

/* 入力項目クリア */
function insFormClear(){
    // フォームクリア
    document.getElementById('place_name').value = "";
    document.getElementById('zip_1').value = "";
    document.getElementById('zip_2').value = "";
    document.getElementById('address').value = "";
    document.getElementById('tel_1').value = "";
    document.getElementById('tel_2').value = "";
    document.getElementById('tel_3').value = "";
    document.getElementById('start_open_time_1').value = "";
    document.getElementById('start_open_time_2').selectedIndex = 0;
    document.getElementById('finish_open_time_1').value = "";
    document.getElementById('finish_open_time_2').selectedIndex = 0;
    document.getElementById('off_week_code').selectedIndex = 0;
    // エラー表示クリア
    errClear();
}

function errClear(){
    document.getElementById('err_main').innerHTML = "";
    document.getElementById('err_place_name').innerHTML = "";
    document.getElementById('err_zip').innerHTML = "";
    document.getElementById('err_address').innerHTML = "";
    document.getElementById('err_tel').innerHTML = "";
    document.getElementById('err_open_time').innerHTML = "";
    document.getElementById('err_off_week').innerHTML = "";
}

// マップから郵便番号取得
function setAddress() {
	var geocoder = new google.maps.Geocoder();
	// 
	geocoder.geocode({ 'location': map.getCenter() }, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK && results[0]) {
			var addressVal = results[0].formatted_address.replace(/^日本, 〒/, '');
			document.getElementById('zip_1').value = addressVal.substr(0,3);
			document.getElementById('zip_2').value = addressVal.substr(4,4);
			document.getElementById('address').value = addressVal.substr(9);
		} else {
alert("Geocode 取得に失敗しました<br />reason: "+ status);
		}
	});
}
