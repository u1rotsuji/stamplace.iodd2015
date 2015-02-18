// オブジェクト定義
var map;						// マップオブジェクト
var markdata;					// 施設・店舗情報
var gMarkerCenter;
var insertWindow;
var markArray = [];				// マーカー格納配列
var windowArray = [];			// メッセージウィンドウ格納配列
var isViewMapTopMenu = true;				// マップ上メニュー表示フラグ
var isViewMapBottomMenu = true;				// マップ下メニュー表示フラグ

// マップの中心位置設定
var centerLat = base_lat;
var centerLng = base_lon;
var zoomSize = map_min_size;

var vImageColor = {0: 'gray', 1: 'yellow', 2: 'white', 3: 'white', 4: 'white'};	// マーカーの色
var vStatus = {0: '<div class="status_base status_off">仮登録</div>', 1: '<div class="status_base status_active">利用可</div>', 2: '<div class="status_base status_close">利用不可</div>', 3: '<div class="status_base status_holiday">利用不可</div>', 4: '<div class="status_base status_holiday">時間未登録</div>'};	// 場所の状態
var weekArray = ['--選択してください--', '日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日', '無休'];

$(document).ready(function(){
    $(".iframe").colorbox({iframe:true, width:"90%", height:"90%"});
});

function detailbox(place_id) {
    // 詳細リンクが押された時の表示画面設定
//    document.getElementById("info_button_colorbox").href = "http://code4kashiwa.sakura.ne.jp/stamplace/map/placedetail.php?pid=" + place_id;
    document.getElementById("info_button_colorbox").href = "placedetail.php?pid=" + place_id;
    document.getElementById("info_button_colorbox").click();
}
function insertbox(lat, lng) {
    // 登録リンクが押された時の表示画面設定
//    document.getElementById("info_button_colorbox").href = "http://code4kashiwa.sakura.ne.jp/stamplace/map/mapplaceset.php?lat=" + lat + "&lng=" + lng;
    document.getElementById("info_button_colorbox").href = "mapplaceset.php?lat=" + lat + "&lng=" + lng;
    document.getElementById("info_button_colorbox").click();
}

/* 地図の初期化 */
function initialize() {

	/* div:map のサイズ設定 */
	dy = window.innerHeight - 100;
	document.getElementById("map").style.height = dy+'px';
        document.getElementById("main").style.height = dy+'px';
	document.getElementById("map_menu_bottom").style.top = (dy + 14)+'px';

	/* 地図のオプション設定 */
	var myOptions={
		/*初期のズーム レベル */
		zoom: map_min_size,
		/* 地図の中心点 */
		center: new google.maps.LatLng(base_lat, base_lon),
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
	map=new google.maps.Map(document.getElementById("map"), myOptions);
        
        /* 中心に十字のマーカーを置く */
/*
        gMarkerCenter = new google.maps.Marker({
            position: new google.maps.LatLng(base_lat, base_lon),
            map: map,
            icon: './img/marker/center.png',
            draggable: true
        });
*/

    /* 店舗データ取得 */
	$.ajax({
		type: "GET",
		dataType: "jsonp",
		data: {
                    "apikey": "1",
                    "res": "jsonp"
		},
		cache: false,
		url: api_protocol + "//" + api_domain + api_path + "getPlaceInfo.php?callback=getplaceinfo",
		success: function(data)
		{
			markdata = data['data'];

			/* マーカーオブジェクト */
			for (i = 0; i < markdata.length; i++) {
				var shop = markdata[i];

				var shop_latlng= new google.maps.LatLng(shop.lat, shop.lng);

				var iconImg = new google.maps.MarkerImage(
					'./img/marker/marker_' + vImageColor[shop.view_status] + '.png'
				);

				var marker = new google.maps.Marker({
					position: shop_latlng,
					map: map,
					icon: iconImg,
					title: shop.place_name
				});

				markArray[i] = marker;

				/* マーカーがクリックされた時 */
				attachMessage(markArray[i], i);

			}
		}
	});

/*
        // 中心位置移動時の処理
        google.maps.event.addListener(map, 'center_changed', function(){
            var pos = map.getCenter();
            gMarkerCenter.setPosition(pos);
            gMarkerCenter.setTitle('map center: ' + pos);
        });

        // 十字のドラッグ＆ドロップ時の処理
        google.maps.event.addListener(gMarkerCenter, 'dragend', function(){
            map.panTo(gMarkerCenter.position);
        });
 
        // 十字のクリック時の処理
        google.maps.event.addListener(gMarkerCenter, 'click', function(){
            if(insertWindow)
                insertWindow.close();
            var pos = map.getCenter();
            insertWindow = new google.maps.InfoWindow({
                content: '<a style="text-decoration: none; color: black;" href="javascript:insertbox(' + pos.lat() + ',' + pos.lng() + ')">この場所を登録</a>'
            });
            insertWindow.open(map, gMarkerCenter);

            centerLat = pos.lat();
            centerLng = pos.lng();
            zoomSize = map.getZoom();
         
            map.setZoom(map_max_size);

            google.maps.event.addListener(insertWindow, 'closeclick', function(){
                moveZoomPosition(centerLat, centerLng, zoomSize);
            });
        });
*/
}

/* マーカークリック時のイベント設定 */
function attachMessage(marker, num) {
	// メッセージ設定
//	var msg = markdata[num].name + "<br /><a href='placedetail.php?m="+ markdata[num].id + "'>詳細</a>";
	var msg = "<div id='info_window'>";
	msg += "<div id='info_name'>" + markdata[num].place_name + "</div>";
	msg += "<div id='info_address'>";
	if(markdata[num].zip.length > 0) {
		msg += "〒" + markdata[num].zip + "<br />";
	}
	msg += markdata[num].address + "</div>";
	msg += "<div id='info_view'>" + vStatus[markdata[num].view_status] + "</div>";
	msg += "<div id='info_button'><a href='javascript:detailbox(" + markdata[num].place_id + ")'>詳細</a></div>";
	msg += "</div>";
	// メッセージウィンドウ定義
	var infowindow = new google.maps.InfoWindow({
		content:msg
	});

    // イベント設定
	google.maps.event.addListener(marker, 'click', function() {
            // クリック時の座標取得
            var centerLatlng = map.getCenter();
            centerLat = centerLatlng.lat();
            centerLng = centerLatlng.lng();
            zoomSize = map.getZoom();

            if(insertWindow)
                insertWindow.close();
//            gMarkerCenter.setVisible(false);

            shopAction(num);
	});
	google.maps.event.addListener(infowindow, 'closeclick', function(){
//            gMarkerCenter.setVisible(true);
            infowindowAllClear();
            moveZoomPosition(centerLat, centerLng, zoomSize);
	});
	// メッセージウィンドウ情報を配列に格納
	windowArray[num] = infowindow;
}

/* 全てのメッセージウィンドウを消す */
function infowindowAllClear() {
	if(windowArray.length > 0) {
		for (i = 0; i < windowArray.length; i++) {
			if(windowArray[i]) windowArray[i].close();
		}
	}
}

/* 選択された位置に指定されたサイズで移動 */
function moveZoomPosition(lat, lng, size) {
	/* 位置設定 */
	var latlng= new google.maps.LatLng(lat, lng);
	/* 選択された位置に移動 */
	map.panTo(latlng);
	/* ズームアップ */
	map.setZoom(size);
}

/* 施設・店舗選択時のアクション */
function shopAction(num) {
	// 選択された位置に移動＆ズームアップ
	var pos = markArray[num].getPosition();
	moveZoomPosition(pos.lat(), pos.lng(), map_max_size);
	// 全てのメッセージウィンドウを消す
	infowindowAllClear();
	// 指定のメッセージウィンドウを表示
	windowArray[num].open(map, markArray[num]);
}

/* マップ表示設定 */
function viewMapSetting() {
	// 中心位置の緯度・経度表示
	var centerLatlng = map.getCenter();
	document.getElementById("map_lat_val").innerHTML = "&nbsp;&nbsp;緯度：" + centerLatlng.lat();
	document.getElementById("map_lng_val").innerHTML = "&nbsp;&nbsp;経度：" + centerLatlng.lng();
	// マップメニュー表示切替チェックボックス
	var chkboxHtml = '<input type="checkbox" id="topctrl" name="topctrl" value="on" onclick="viewMapTopMenu(this)" ##CHECKED00##/>タイトル・登録メニュー';
        chkboxHtml += '<br><input type="checkbox" id="zoomctrl" name="zoomctrl" value="on" onclick="viewMapBottomMenu(this)" ##CHECKED01##/>レイヤー・ズーム';
	var viewTopmenu = $('#map_menu_top').is(':visible');
	var viewMapmenu = $('#map_menu_bottom').is(':visible');
	if(viewTopmenu) {
		chkboxHtml = chkboxHtml.replace("##CHECKED00##", " checked");
	} else {
		chkboxHtml = chkboxHtml.replace("##CHECKED00##", "");
	}
	if(viewMapmenu) {
		chkboxHtml = chkboxHtml.replace("##CHECKED01##", " checked");
	} else {
		chkboxHtml = chkboxHtml.replace("##CHECKED01##", "");
	}
	document.getElementById("map_menu_val").innerHTML = chkboxHtml;
}

/* マップタイプ変更 */
function setMapType(obj) {
	var selectMap = obj.options.item(obj.selectedIndex).value;
	switch ( selectMap ) {
	  // 道路地図（デフォルト）
		case "0":
			map.setMapTypeId(google.maps.MapTypeId.ROADMAP);
			break;
		// Google Earth の航空写真
		case "1":
			map.setMapTypeId(google.maps.MapTypeId.SATELLITE);
			break;
		// 通常のビューと航空写真を混合
	  case "2":
			map.setMapTypeId(google.maps.MapTypeId.HYBRID);
			break;
		// 地形情報に基づいて物理的なマップタイル
	  case "3":
			map.setMapTypeId(google.maps.MapTypeId.TERRAIN);
	}
}

/* マップコントロール表示設定 */
function viewMapTopMenu(obj) {
	var swt = obj.checked;
	if(swt) {
		document.getElementById("map_menu_top").style.display="block";
	} else {
		document.getElementById("map_menu_top").style.display="none";
	}
}
function viewMapBottomMenu(obj) {
	var swt = obj.checked;
	if(swt) {
		document.getElementById("map_menu_bottom").style.display="block";
	} else {
		document.getElementById("map_menu_bottom").style.display="none";
	}
}
/* ズームアップ */
function zoomUp() {
	var nowzoom = map.getZoom();
	map.setZoom(nowzoom + 1);
}

/* ズームダウン */
function zoomDown() {
	var nowzoom = map.getZoom();
	map.setZoom(nowzoom - 1);
}

/* 特定のエリアを拡大表示 */
function viewNear(){
	// 全てのメッセージウィンドウを消す
	infowindowAllClear();
	// 選択された位置に移動＆ズームダウン
	moveZoomPosition(base_lat, base_lon, 16);
}

/* 広域選択 */
function viewAll(){
	// 全てのメッセージウィンドウを消す
	infowindowAllClear();
	// 選択された位置に移動＆ズームダウン
	moveZoomPosition(base_lat, base_lon, map_min_size);
}

/* 現在位置に移動 */

/* GPS緯度経度取得 */
function getGPS() {
	var gps = navigator.geolocation;
	gps.getCurrentPosition(updatePosition, handleError, { enableHighAccuracy: true, timeout: 10000 });
}

/* GPS緯度経度の表示 */
function updatePosition(position) {
	var gpslat = position.coords.latitude;
	var gpslng = position.coords.longitude;
	// 選択された位置に移動＆ズームアップ
	moveZoomPosition(gpslat, gpslng, map_max_size);
}

/* エラー処理 */
function handleError(positionError) {
alert("GPSでの位置情報取得に失敗しました。<br />ErrorMsg: " + positionError.message);
}
