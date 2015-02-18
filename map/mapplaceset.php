<?php 
require_once('../module/PlaceModule.php');

mb_language("Japanese");
mb_internal_encoding("utf-8"); //内部文字コードを変更

$errStr = "";

// PlaceModule呼び出し
$placeModule = new PlaceModule();

?>
<!DOCTYPE html> 
<html lang="ja">
	<head>
		<meta content='text/html; charset=UTF-8' http-equiv='Content-Type'/>
		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1">
		<meta name="apple-mobile-web-app-capable" content="yes" />
                <meta http-equiv="Pragma" content="no-cache">
                <meta http-equiv="Cache-Control" content="no-cache">
		<title>場所登録 | StamPlace - StamPlaceデモサイト</title>
		<!-- CSS -->
		<link rel="stylesheet" href="./css/style.box.css">
		<!-- jQuery -->
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<!-- Google Maps APIの読み込み -->
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
		<!-- オリジナルスクリプト -->
		<script type="text/javascript" src="./js/map_common.js"></script>
		<script type="text/javascript" src="./js/map_placeset.js"></script>
	</head>

	<body onload="initialize(<?php print $placeModule->params["lat"]; ?>,<?php print $placeModule->params["lng"]; ?>)">


<?php require_once('_parts_box_header.inc') ?>

<?php if(strlen($errStr) > 0) { ?>            
            <div id="err_msg">
              <?php print $errStr; ?>
            </div>
<?php } ?>            
            <div id="viewplacename">場所情報登録</div>
            <div id="infoset_params">
            	指定した位置の情報を入力し、「登録」ボタンを押してください。
            </div>
            
            <div id="box_main">
<form name="frm" method="POST">

    <a name="mapview"></a>
    <div id="detail_1st" class="parts_details">
      <div id="infoset_params" class="set_center">
          <div id="map_detail"></div>
      </div>
	  <div id="spc5"></div>
	  <input type="button" id="clearBtn" name="clearBtn" value="入力内容をクリア" onclick="javascript:insFormClear();" class="act_button_input_lightblue" />
	  <div id="spc5"></div>
	  <input type="button" id="submitBtn" name="submitBtn" value="登録" onclick="javascript:setPlaceInfo();" class="act_button_input_darkblue" />
      <div id="spc3"></div>
    </div>

    <a name="detailview"></a>
    <div id="detail_2nd" class="parts_details">
      <div id="spc3"></div>
	  <div id="infoset_params_title">場所の名前</div>
	  <div id="infoset_params">
	      <input type="text" id="place_name" name="place_name" class="infoset_params_textbox" size="50" maxlength="40" />
	      <div id="err_place_name" class="errmsg"></div>
	  </div>
	  <div id="infoset_params_title">郵便番号</div>
	  <div id="infoset_params">
	      <input type="text" id="zip_1" name="zip_1" class="infoset_params_textbox" size="4" maxlength="3" style="ime-mode:inactive" />-<input type="text" id="zip_2" name="zip_2" class="infoset_params_textbox" size="5" maxlength="4" style="ime-mode:inactive" />
	      <div id="err_zip" class="errmsg"></div>
	  </div>
	  <div id="infoset_params_title">住所</div>
	  <div id="infoset_params">
	      <input type="text" id="address" name="address" class="infoset_params_textbox" size="50" maxlength="60" />
	      <div id="err_address" class="errmsg"></div>
	  </div>
	  <div id="infoset_params_title">Tel</div>
	  <div id="infoset_params">
	      <input type="text" id="tel_1" name="tel_1" class="infoset_params_textbox" size="4" maxlength="4" style="ime-mode:inactive" />-<input type="text" id="tel_2" name="tel_2" class="infoset_params_textbox" size="4" maxlength="4" style="ime-mode:inactive" />-<input type="text" id="tel_3" name="tel_3" class="infoset_params_textbox" size="4" maxlength="4" style="ime-mode:inactive" />
	      <div id="err_tel" class="errmsg"></div>
	  </div>
	  <div id="spc3"></div>
	  <div id="infoset_params_title">営業時間</div>
	  <div id="infoset_params">
	      <input type="text" id="start_open_time_1" name="start_open_time_1" class="infoset_params_textbox" size="2" maxlength="2" style="ime-mode:inactive" />:<select id="start_open_time_2" name="start_open_time_2" class="infoset_params_select"><option value="00">00</option><option value="15">15</option><option value="30">30</option><option value="45">45</option></select>～<input type="text" id="finish_open_time_1" name="finish_open_time_1" class="infoset_params_textbox" size="2" maxlength="2" style="ime-mode:inactive" />:<select id="finish_open_time_2" name="finish_open_time_2" class="infoset_params_select"><option value="00">00</option><option value="15">15</option><option value="30">30</option><option value="45">45</option></select>
	      <div id="err_open_time" class="errmsg"></div>
	  </div>
	  <div id="infoset_params_title">定休日</div>
	  <div id="infoset_params">
		<select id="off_week_code" name="off_week_code" class="infoset_params_select">
<?php foreach($placeModule->weekStrArray as $wkNum => $wkStr) { ?>
			<option value="<?php print $wkNum; ?>"><?php print $wkStr; ?></option>
<?php } ?>
		</select>
	      <div id="err_off_week" class="errmsg"></div>
	  </div>
	<div id="spc3"></div>                

 </form>
           </div>

<?php require_once('_parts_box_footer.inc') ?>

<script>$('map_detail').attr('readonly', true);</script>

<!-- iPhoneのURLバーを消す -->
<script>setTimeout(scrollTo, 100, 0, 1);</script>

	</body>
</html>
