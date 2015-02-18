<?php 
require_once('../module/PlaceModule.php');

mb_language("Japanese");
mb_internal_encoding("utf-8"); //内部文字コードを変更

$errStr = "";

// PlaceModule呼び出し
$placeModule = new PlaceModule();
// place_idチェック
if(!$placeModule->pidChk())
{
    $errStr = $placeModule->errStr;
}
else
{
    // データ取得
    $data = $placeModule->getPlaceDetailSql($placeModule->params);
}

?>
<!DOCTYPE html> 
<html lang="ja">
	<head>
		<meta content='text/html; charset=UTF-8' http-equiv='Content-Type'/>
		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1">
		<meta name="apple-mobile-web-app-capable" content="yes" />
                <meta http-equiv="Pragma" content="no-cache">
                <meta http-equiv="Cache-Control" content="no-cache">
		<title>場所詳細 | StamPlace - StamPlaceデモサイト</title>
		<!-- CSS -->
		<link rel="stylesheet" href="./css/style.box.css">
		<!-- jQuery -->
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<!-- Google Maps APIの読み込み -->
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
		<!-- オリジナルスクリプト -->
		<script type="text/javascript" src="./js/map_common.js"></script>
		<script type="text/javascript" src="./js/map_placedetail.js"></script>
	</head>

	<body onload="initialize(<?php print$data["lat"]; ?>,<?php print$data["lng"]; ?>,<?php print$data["view_status"]; ?>)">


<?php require_once('_parts_box_header.inc') ?>

<?php if(strlen($errStr) > 0) { ?>            
            <div id="err_msg">
              <?php print $errStr; ?>
            </div>
<?php } else { ?>            
            <div id="viewplacename"><?php print$data["place_name"]; ?></div>
            
            <div id="box_detail">

    <a name="mapview"></a>
    <div id="detail_1st" class="parts_details">
      <div id="infoset_params" class="set_center">
          <div id="map_detail"></div>
      </div>
      <div id="spc3"></div>
      <div id="infoset_params_title">現在の状況</div>
      <div id="infoset_params" class="set_center">
          <div class="set_center"><div id="infoset_status_<?php print $data["view_status"]; ?>"><?php print $placeModule->statusStrArray[$data["view_status"]]; ?></div></div>
      </div>
    </div>

    <a name="detailview"></a>
    <div id="detail_2nd" class="parts_details">
      <div id="spc3"></div>
<?php   if(strlen($data["zip"]) > 0 || strlen($data["address"]) > 0) { ?>
      <div id="infoset_params_title">住所</div>
      <div id="infoset_params"><?php if(strlen($data["zip"]) > 0) {?>〒<?php print $data["zip"]; ?><br><?php } ?><?php print $data["address"]; ?></div>
<?php   } ?>            
<?php   if(strlen($data["tel"]) > 2) { ?>
      <div id="infoset_params_title">Tel</div>
      <div id="infoset_params"><?php print $data["tel"]; ?></div>
<?php   } ?>            
<?php   if(strlen($data["open_from"]) > 0 && strlen($data["open_to"]) > 0) { ?>
      <div id="infoset_params_title">営業時間</div>
      <div id="infoset_params"><?php print $data["open_from"]; ?> ～ <?php print $data["open_to"]; ?></div>
<?php   } ?>            
<?php   if(strlen($data["off_week_code"]) > 0) { ?>
<!--
      <div id="infoset_params_title">定休日</div>
      <div id="infoset_params"><?php print $placeModule->weekStrArray[$data["off_week_code"]]; ?></div>
-->
<?php   } ?>            
    </div>
                <div id="spc3"></div>                

            </div>
<?php } ?>            

<?php require_once('_parts_box_footer.inc') ?>

<script>$('map_detail').attr('readonly', true);</script>

<!-- iPhoneのURLバーを消す -->
<script>setTimeout(scrollTo, 100, 0, 1);</script>

	</body>
</html>





