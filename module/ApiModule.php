<?php
/*
 *  アクセスマップ表示モジュール
 */

//require_once(dirname(__FILE__) . "/BaseModule.inc");
//require_once('/home/code4kashiwa/www/stamplace/module/BaseModule.php');
require_once('../module/BaseModule.php');

class ApiModule extends baseModule
{
	// リクエストパラメータ格納領域
//	var $params = array();
	// APIキー
	var $apiKey = null;
	// APIパラメータ情報
	var $apiResType = "json";
	var $apiEnc = "utf8";
	// APIレスポンス情報
	var $apiHeaderInfo;
	var $apiBodyInfo = "";
	// ログ情報
	var $logInfo;
	// 文字エンコード設定
	var $encArray = array("jis" => "ISO-2022-JP", "sjis" => "SJIS_win", "euc" => "EUCJP_win", "utf8" => "utf-8");
	// 入力エラー情報格納領域
	var $errArray = array();

/*
 *  初期処理
 */
	function __construct()
	{
		parent::__construct();

		// パラメータ定義
		$this->getParams();
		// APIパラメータ設定
		$this->initResParams();

		return true;
	}

/*
 *  DB接続＆API認証
 */
	function initApi()
	{
		// DB接続
		$this->connSite = $this->getConnection(ADMINDBNAME, DBSERVER, BASEDBUSER, BASEDBPASS);

		// APIキー取得
		$this->apiKey = $this->params[PRM_API_KEY];
		if($this->apiKey == null || strlen($this->apiKey) == 0)
		{
			$this->setErrorInfo("APIキーの取得に失敗しました。");
			throw new BaseModuleException($this->errMsg);
			return false;
		}

		return true;
	}

/*
 *  出力ログレベル設定
 */
	function setLoglevel($logLevel = SP_LOG_INFO)
	{
		if(array_key_exists($logLevel, $this->logLevelStr))
			$this->logLv = $logLevel;
		else
			$this->logLv = SP_LOG_INFO;

		return true;
	}

/*
 *  APIキー認証（あとで作る）
 */
	function isAuthApiKey()
	{
		$this->logWrite(SP_LOG_INFO, "AuthApiKey:[" . $this->apiKey . "]");
/*

		// アクセス日時取得
		session_start();
		$this->accessTime = $_SESSION['ACCESS'];
		// ベースキー・アクセス日時・最終ログイン日時より管理者情報取得
		if(!$this->getAdminInfo()) return false;
		// 取得したサイトIDよりサイト情報取得
		if(!$this->getSiteInfo()) return false;
		// アクセス日時の更新
		$this->accessTime = date("YmdHis");
		// アクセス日時更新
		if(!$this->updateAccessTime()) return false;
		// セッション情報設定
		session_start();
		$_SESSION['ACCESS'] = $this->accessTime;
		// ログインフラグ設定
		$this->isLogin = true;
*/

		return true;
	}

/*
*  レスポンスパラメータ設定
*/
	function initResParams() {
		// レスポンス種別
		if(array_key_exists(PRM_RESTYPE, $this->params))
			$this->apiResType = $this->params[PRM_RESTYPE];
		// 文字エンコード
		if(array_key_exists(PRM_RESENC, $this->params) && array_key_exists($this->params[PRM_RESENC], $this->encArray))
			$this->apiEnc = $this->params[PRM_RESENC];

		return true;
	}

/*
*  レスポンス情報編集
*/
	function makeSuccessResponse($result = null) {
		$resArray = array();
		
		$resArray[RES_STATUS] = STATUS_OK;
		$resArray[RES_DATA] = $result;
		
		return $resArray;
	}

	function makeErrorResponse($result = null) {
		$resArray = array();
		
		$resArray[RES_STATUS] = STATUS_NG;
		$errInfo = (is_array($result)) ? $result : array("errmsg" => $result);
		$resArray[RES_ERROR] = $errInfo;
		
		return $resArray;
	}

/*
 *  レスポンス情報の作成
 */
	function makeResponse($resParams)
	{
		// 文字エンコード設定（UTF-8以外のものを変換）
		if($this->apiEnc != "utf8")
		{
			mb_convert_variables($this->encArray[$this->apiEnc], 'UTF-8', $resParams);
		}
		// レスポンスの種別により出力内容を変える
		switch ($this->apiResType) {
			case "json":
				$encode = json_encode($resParams);
				$this->apiHeaderInfo = "Content-Type: text/javascript; charset=" . $this->encArray[$this->apiEnc];
				$this->apiBodyInfo = $encode;
				break;
			case "jsonp":
				$callback = $_GET["callback"];
				$encode = json_encode($resParams);
				$this->apiHeaderInfo = "Content-Type: text/javascript; charset=" . $this->encArray[$this->apiEnc];
				$this->apiBodyInfo = $callback . "(" . $encode. ")";
				break;
			case "xml":
				$this->apiHeaderInfo = "Content-Type: text/xml; charset=" . $this->encArray[$this->apiEnc];
				$xmlstr = "<?xml version=\"1.0\" ?><result></result>";
				$xml = new SimpleXMLElement($xmlstr);
				foreach($resParams as $arrKey => $arrVal)
				{
					if(!is_array($arrVal))
					{
						$xmlitem = $xml->addChild($arrKey, $arrVal);
					}
					else
					{
						$xmlitem = $xml->addChild("item");
						foreach($arrVal as $xmlKey => $xmlVal)
						{
							$xmlitem->addChild($xmlKey, $xmlVal);
						}
					}
				}
				$this->apiBodyInfo = $xml->asXML();
				break;
		}

		return true;
	}

/*
 *  SELECT文実行＆結果抽出
 */
	function getSelectData($sql, $cond = null)
	{
		$result = array();

		$rows = $this->queryExec($this->connSite, $sql, $cond);
		$result = $rows->fetchAll(PDO::FETCH_ASSOC);

		return $result;
	}

/*
*  入力チェック
*  （正規表現のパターンを随時ここに追加）
*/
	function checkParameter($prmKey, $chkType, $minByte = 1, $maxByte = 1, $isNull = true) {
		$regText = "";
		// 入力文字変換と正規表現設定
		switch($chkType) {
			// 数値（半角数字のみ）
			case "int":
				$regText = "/^[0-9]+$/";
				break;
			// 数値（半角数字＋小数点）
			case "numeric":
				$regText = "/^[0-9]+(\.[0-9]*)?$/";
				break;
			// 日本語入力、数字や記号等は全角文字に
			case "jp":
				$this->params[$prmKey] = mb_convert_kana($this->params[$prmKey], 'KVRN');
				break;
			default:
		}
		// 入力文字のサニタイズ
		$this->params[$prmKey] = $this->encodeHtmlStr($this->params[$prmKey]);
		// チェック値設定
		$val = $this->params[$prmKey];
		// 必須入力チェック
		if(!$isNull && strlen($val) < 1) {
			$this->errArray[$prmKey] = "必須入力項目です。必ず入力してください。";
			return false;
		}
		// 文字列長チェック
		if(strlen($val) > 0 && strlen($val) < $minByte) {
			$this->errArray[$prmKey] = "入力した文字が（およそ" . ($minByte - strlen($val)) . "文字ほど）短いです。";
			return false;
		}
		if(strlen($val) > 0 && strlen($val) > $maxByte) {
			$this->errArray[$prmKey] = "入力した文字が（およそ" . (strlen($val) - $maxByte) . "文字ほど）長いです。";
			return false;
		}
		// 正規表現によるチェック
		if(strlen($val) > 0 && strlen($regText) > 0 && !preg_match($regText, $val)) {
			$this->errArray[$prmKey] = "入力形式が正しくありません。";
			return false;
		}
		
		$this->errArray[$prmKey] = "";
		return true;
	}

/*
*  入力エラー情報取得
*/
	function getFormErrInfo() {
		return $this->errArray;
	}

/*
*  表示ステータス取得SQL文字列生成
*  （仕様かたまったらviewにするかも）
*/
	function getViewStatus() {
		$sqlStr  = "CASE WHEN p.status = 0 THEN 0";
		$sqlStr .= " WHEN ( pa.place_id IS NULL OR pa.status = 0 ) THEN 4";
		$sqlStr .= " WHEN ( DAYOFWEEK(now()) = pa.off_week_code ) THEN 3";
		$sqlStr .= " WHEN (";
		$sqlStr .= " ( pa.start_open_time >= now() OR now() >= pa.finish_open_time )";
		$sqlStr .= ") THEN 2";
		$sqlStr .= " ELSE 1 END as view_status";

		return $sqlStr;
	}

/*
 *  終了処理
 */
	function __destruct() {
		return true;
	}

}
?>
