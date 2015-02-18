<?php

//require_once(dirname(__FILE__) . "/../module/ApiModule.inc");
//require_once('/home/code4kashiwa/www/stamplace/module/ApiModule.php');
require_once('../module/ApiModule.php');

// デバッグモード
if(DEBUGMODE)
{
	ini_set("display_errors", "On");
	error_reporting(E_ALL);
//	error_reporting(E_WARNING);
}

class getPlaceDetailModule
{
	var $api;
/*
 *  初期処理
 */
	function __construct()
	{
		$this->api = new ApiModule();
		return true;
	}

/*
 *  API制御処理
 */
	function execute()
	{
		$this->api->logWrite(SP_LOG_INFO, "getPlaceDetail Start");

		try {
			// DB接続＆API認証
			$this->api->initApi();
			// APIキー認証
			$this->api->isAuthApiKey();
			// 位置情報取得
			$place = $this->getPlaceSql($this->api->params);
			// ステータス設定
			$result = $this->api->makeSuccessResponse($place);
		}
		catch (BaseModuleException $e)
		{
			$result = $this->api->makeErrorResponse($e->getMessage());
		}

		// レスポンス情報編集
		$this->api->makeResponse($result);

		$this->api->logWrite(SP_LOG_INFO, "getPlaceDetail Finish");
	}

/*
 *  位置情報取得SQL文編集
 */
	function getPlaceSql($params = null)
	{
		//
		// 抽出条件の設定
		$colArray = array(
			$params[PLACE_ID]
		);
		// SQL文の編集
		$sqlStr  = "SELECT p.place_id";                 // 場所ID
		$sqlStr .= ", p.lat";				// 緯度
		$sqlStr .= ", p.lng";				// 緯度
		$sqlStr .= ", p.place_name";                    // 場所の名称
		$sqlStr .= ", p.zip";                           // 郵便番号
		$sqlStr .= ", p.address";                       // 住所
		$sqlStr .= ", p.tel";                           // 電話番号
		$sqlStr .= ", p.status";			// レコードステータス
		$sqlStr .= ", " . $this->api->getViewStatus();  // 表示ステータス
		$sqlStr .= ", pa.image_url";			// 画像URL
		$sqlStr .= ", DATE_FORMAT(pa.start_open_time, '%H:%i') as open_from";    // 利用可能時間（From）
		$sqlStr .= ", DATE_FORMAT(pa.finish_open_time, '%H:%i') as open_to";     // 利用可能時間（To）
		$sqlStr .= ", pa.off_week_code";		// 定休日の曜日
		$sqlStr .= ", pa.url";				// HPとかのURL
		$sqlStr .= ", pa.email";			// メールアドレス
		$sqlStr .= ", pa.parking_flag";			// 駐車場フラグ
		$sqlStr .= ", pa.items";			// 取り扱い産品
		$sqlStr .= " FROM place p";
		$sqlStr .= " LEFT JOIN place_advance pa ON p.place_id = pa.place_id";
		$sqlStr .= " WHERE p.place_id = ?";
		$sqlStr .= " ORDER BY p.last_update desc limit 1";

		$result = $this->api->getSelectData($sqlStr, $colArray);

		return $result[0];
	}

/*
 *  終了処理
 */
	function __destruct() {
		return true;
	}
}

$module = new getPlaceDetailModule();
$module->execute();
header($module->api->apiHeaderInfo);
print $module->api->apiBodyInfo;
?>
