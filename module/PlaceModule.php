<?php
/*
 *  位置情報（place, place_advance）閲覧・編集モジュール
 */

//require_once(dirname(__FILE__) . "/BaseModule.inc");
//require_once('/home/code4kashiwa/www/stamplace/module/BaseModule.php');
require_once('../module/BaseModule.php');

class PlaceModule extends baseModule
{
	// リクエストパラメータ格納領域
//	var $params = array();
	// エラー情報
	var $errStr = "データの呼び出しに失敗しました。";
	// 入力エラー情報格納領域
	var $errArray = array();

	// ステータス表示設定
	var $statusStrArray = array(
		  0 => "仮登録"
		, 1 => "利用可"
		, 2 => "利用不可（時間外）"
		, 3 => "利用不可（終日）"
		, 4 => "時間未設定"
	);
	// 曜日表示設定
	var $weekStrArray = array(
		  0 => "--選択してください--"
		, 1 => "日曜日"
		, 2 => "月曜日"
		, 3 => "火曜日"
		, 4 => "水曜日"
		, 5 => "木曜日"
		, 6 => "金曜日"
		, 7 => "土曜日"
		, 8 => "無休"
	);

/*
 *  初期処理
 */
	function __construct()
	{
		parent::__construct();

		// パラメータ定義
		$this->getParams();
		// DB接続
		$this->connSite = $this->getConnection(ADMINDBNAME, DBSERVER, BASEDBUSER, BASEDBPASS);

		return true;
	}

/*
*  place_id リクエストパラメータチェック
*/
	function pidChk() {
		// place_id
		if(count($this->params) != 1 || !array_key_exists(PLACE_ID, $this->params))
		{
			$this->setErrorInfo("余計なパラメータが設定されているか、必要なパラメータがありません。");
			return false;
		}
		else if(!preg_match("/^[0-9]+$/", $this->params[PLACE_ID]))
		{
			$this->setErrorInfo(PLACE_ID . "の形式が正しくありません。");
			return false;
		}

		return true;
	}

/*
 *  位置情報
 */
	function getSelectData($sql, $cond = null)
	{
		$result = array();

		$rows = $this->queryExec($this->connSite, $sql, $cond);
		$result = $rows->fetchAll(PDO::FETCH_ASSOC);

		return $result;
	}

/*
 *  位置情報取得SQL文編集
 */
	function getPlaceSql($params = null)
	{
		// SQL文の編集
		$sqlStr  = "SELECT p.place_id";		// 場所ID
		$sqlStr .= ", p.lat";							// 緯度
		$sqlStr .= ", p.lng";							// 緯度
		$sqlStr .= ", p.place_name";			// 場所の名称
		$sqlStr .= ", p.zip";							// 郵便番号
		$sqlStr .= ", p.address";					// 住所
		$sqlStr .= ", p.tel";							// 電話番号
		$sqlStr .= ", p.status";					// レコードステータス
		$sqlStr .= ", " . $this->getViewStatus();	// 表示ステータス
		$sqlStr .= " FROM place p";
		$sqlStr .= " LEFT JOIN place_advance pa ON p.place_id = pa.place_id";
		$sqlStr .= " WHERE p.status >= 0";

		$result = $this->getSelectData($sqlStr);

		return $result;
	}

	function getPlaceDetailSql($params = null)
	{
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
		$sqlStr .= ", " . $this->getViewStatus();  // 表示ステータス
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

		$result = $this->getSelectData($sqlStr, $colArray);

		return $result[0];
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
