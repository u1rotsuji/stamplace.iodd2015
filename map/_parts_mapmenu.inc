<!-- 場所登録メニュー -->
<div id="map_menu_top">
	<div id="map_menu_under" class="menu_layout_under"></div>
	<div id="map_menu_over" class="menu_layout_over">
		<div id="map_menu_left">
			<div id="site_title" class="map_menu_left_title">サイト名：柏＆流山&nbsp;赤ちゃんほっとステーション IODD2015</div>
		</div>
		<div id="map_menu_right">
			<a class="map_menu_right_insert" href="javascript:getGPS();">現在位置に移動</a>
		</div>
	</div>
</div>

<!-- マップ検索メニュー -->
<div id="map_menu_bottom">
	<div id="map_menu_under" class="menu_layout_under"></div>
	<div id="map_menu_over" class="menu_layout_over">
		<div id="map_menu_left">
			<div class="map_menu_left_title">レイヤー：</div>
			<div class="map_menu_left_detail">
				<select id="maptype" name="maptype" class="infoset_params_select" onChange="setMapType(this)">
					<option value="0">道路地図</option>
					<option value="2">航空写真</option>
					<option value="3">マップタイル&nbsp;&nbsp;</option>
				</select>
			</div>
		</div>
		<div id="map_menu_right">
			<a href="javascript:zoomDown();"><img src="./img/button/zoom_down.gif" border="0"></a>
			<a href="javascript:zoomUp();"><img src="./img/button/zoom_up.gif" border="0"></a>
		</div>
	</div>
</div>
