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

class insertPlaceInfoModule
{
	var $api;
	var $params;
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
		$this->api->logWrite(SP_LOG_INFO, "insertPlaceInfo Start");

		try {
			// DB接続＆API認証
			$this->api->initApi();
			// APIキー認証
			$this->api->isAuthApiKey();
			// 入力チェック
			if($this->checkInput()) {
				// データ登録
				$this->api->beginExec($this->api->connSite);	// トランザクション開始
				$this->insertPlaceInfo($this->api->params);
				$this->insertPlaceAdvanceInfo($this->api->params);
				$this->api->commitExec($this->api->connSite);	// コミット
				// ステータス設定
				$result = $this->api->makeSuccessResponse();
			} else {
				$result = $this->api->makeErrorResponse($this->api->getFormErrInfo());
			}
		}
		catch (BaseModuleException $e)
		{
			$this->api->rollbackExec($this->api->connSite);		// ロールバック
			$result = $this->api->makeErrorResponse($e->getMessage());
		}

		// レスポンス情報編集
		$this->api->makeResponse($result);

		$this->api->logWrite(SP_LOG_INFO, "insertPlaceInfo Finish");
	}

/*
 *  入力チェック
 */
	function checkInput()
	{
		$errArray = array();
		$errArray[] = $this->api->checkParameter("place_name", "jp", 1, 80, false);
		$errArray[] = $this->api->checkParameter("lat", "numeric", 3, 18, false);
		$errArray[] = $this->api->checkParameter("lng", "numeric", 3, 18, false);
		$errArray[] = $this->api->checkParameter("zip_1", "int", 3, 3, false);
		$errArray[] = $this->api->checkParameter("zip_2", "int", 4, 4, false);
		$errArray[] = $this->api->checkParameter("address", "jp", 1, 120, false);
		$errArray[] = $this->api->checkParameter("tel_1", "int", 2, 4, true);
		$errArray[] = $this->api->checkParameter("tel_2", "int", 2, 4, true);
		$errArray[] = $this->api->checkParameter("tel_3", "int", 2, 4, true);
		$errArray[] = $this->api->checkParameter("start_open_time_1", "int", 1, 2, false);
		$errArray[] = $this->api->checkParameter("start_open_time_2", "int", 1, 2, false);
		$errArray[] = $this->api->checkParameter("finish_open_time_1", "int", 1, 2, false);
		$errArray[] = $this->api->checkParameter("finish_open_time_2", "int", 1, 2, false);
		$errArray[] = $this->api->checkParameter("off_week_code", "int", 1, 1, true);

		// エラー数をカウント
		$errCount = 0;
		foreach($this->api->errArray as $keyStr => $errStr) {
$this->api->logWrite(SP_LOG_INFO, $errStr);
			if(!is_null($errStr) && strlen($errStr) > 0)
				$errCount ++;
		}

		$this->api->logWrite(SP_LOG_INFO, "insertPlaceInfo:checkInput error count: ".$errCount);
//		if(DEBUGMODE)
//		{
			if($errCount > 0 ) {
				$errDump = print_r($this->api->errArray, true);
				$this->api->logWrite(SP_LOG_INFO, "errors: ".$errDump);
			}
//		}
		return ($errCount == 0);
	}

/*
 *  位置情報登録
 */
	function insertPlaceInfo($params = null)
	{
		// 値の設定
		$valueArray = array(
			  $params["lat"]
			, $params["lng"]
			, $params["place_name"]
			, $params["zip_1"] . "-" . $params["zip_2"]
			, $params["address"]
			, $params["tel_1"] . "-" . $params["tel_2"] . "-" . $params["tel_3"]
			, VIEW_INIT
		);
		// SQL文の編集
		$sqlStr  = "INSERT INTO place (";
		$sqlStr .= " lat, lng, place_name, zip, address, tel, insert_date, insert_user, last_update, update_user, status";
		$sqlStr .= ") VALUES (?, ?, ?, ?, ?, ?, now(), 0, now(), 0, ?)";

		$result = $this->api->actionExec($this->api->connSite, $sqlStr, $valueArray);

		$this->api->logWrite(SP_LOG_INFO, "insertPlaceInfo:insertPlaceInfo sql: " . $sqlStr);
//		if(DEBUGMODE)
//		{
			$prmDump = print_r($valueArray, true);
			$this->api->logWrite(SP_LOG_INFO, "insertPlaceInfo:insertPlaceInfo params: ".$prmDump);
//		}

		return $result;
	}

/*
 *  位置情報インクリメント値取得
 */
	function getPlaceId()
	{
		// SQL文の編集
		$sqlStr  = "select last_insert_id() as place_id";

		$result = $this->api->getSelectData($sqlStr);

		$resDump = print_r($result, true);
		$this->api->logWrite(SP_LOG_INFO, "insertPlaceInfo:getPlaceId result: " . $resDump);

		return $result[0]["place_id"];
	}

/*
 *  位置付加情報登録
 */
	function insertPlaceAdvanceInfo($params = null)
	{
		// インクリメント値の取得
		$place_id = $this->getPlaceId();
		// 時刻の設定（開始時刻＞終了時刻の場合）
		if($params["start_open_time_1"] * 100 + $params["start_open_time_2"] > $params["finish_open_time_1"] * 100 + $params["finish_open_time_2"])
			$params["finish_open_time_1"] += 24;
		// 値の設定
		$valueArray = array(
			  $place_id
			, null
			, $params["start_open_time_1"] . ":" . $params["start_open_time_2"] . ":00"
			, $params["finish_open_time_1"] . ":" . $params["finish_open_time_2"] . ":00"
			, $params["off_week_code"]
			, null
			, null
			, null
			, null
			, VIEW_INIT
		);
		// SQL文の編集
		$sqlStr  = "INSERT INTO place_advance (";
		$sqlStr .= " place_id, image_url, start_open_time, finish_open_time, off_week_code, url, email, parking_flag, items";
		$sqlStr .= ", insert_date, insert_user, last_update, update_user, status";
		$sqlStr .= ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, now(), 0, now(), 0, ?)";

		$result = $this->api->actionExec($this->api->connSite, $sqlStr, $valueArray);

		$this->api->logWrite(SP_LOG_INFO, "insertPlaceInfo:insertPlaceAdvanceInfo sql: " . $sqlStr);
//		if(DEBUGMODE)
//		{
			$prmDump = print_r($valueArray, true);
			$this->api->logWrite(SP_LOG_INFO, "insertPlaceInfo:insertPlaceAdvanceInfo params: ".$prmDump);
//		}

		return $result;
	}

/*
 *  終了処理
 */
	function __destruct() {
		return true;
	}
}

$module = new insertPlaceInfoModule();
$module->execute();
header($module->api->apiHeaderInfo);
print $module->api->apiBodyInfo;
?>
