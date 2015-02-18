<?php
/*
 *  全機能共通モジュール
 */

//require_once(dirname(__FILE__) . "/Init.inc");
//require_once('/home/code4kashiwa/www/stamplace/module/Init.inc');
require_once('../module/Init.inc');

// アプリケーション内での例外を返す
class BaseModuleException extends Exception { }

class baseModule
{
	// 画面識別
	var $appName = '';
	// リクエストパラメーター
	var $requestType = "GET";
	var $params = array();
	// 表示テンプレート
	var $viewTemplate;
	// エラー判定
	var $isErr = false;
	var $errMsg = "";					// エラーメッセージ
	// DBコネクション
	var $connAdmin = null;				// 管理DB
	var $connSite = null;				// サイト指定DB
	// DBエラー
//	var $dbErrDB = "";					// エラーが発生した接続元のデータベース
	var $dbErrMsg = null;				// MySQLが発行したエラーメッセージ
	var $dbErrCmd = "";					// エラーが発生したmysqlコマンド
	var $dbErrQuery = "";				// エラーが発生したSQL文
	// ログ出力
	var $logLv = SP_LOG_INFO;
	var $logLevelStr = array(SP_LOG_DEBUG => "DEBUG   ", SP_LOG_INFO => "INFO    ", SP_LOG_WARNING => "WARNING ", SP_LOG_ERROR => "ERROR   ", SP_LOG_CRITICAL => "CRITICAL");

/*
 *  初期処理
 */
	function __construct()
	{
		// 内部文字コードをUTF-8に設定
		mb_language("Japanese");
		mb_internal_encoding("utf-8");
	}

	/*
	 *  管理機能の最初に呼び出す
	 */
	function initAdmin($ap)
	{
		// 画面設定
		$this->appName = $ap;
		// 管理DB接続
		$this->connAdmin = $this->getConnection(ADMINDBNAME, DBSERVER, BASEDBUSER, BASEDBPASS);
	}

/*
 *  ログ出力
 */
	function logWrite($logLevel = SP_LOG_INFO, $textStr = "")
	{
		if($logLevel >= $this->logLv) {
			// ログファイル名定義
			$fpathStr = str_replace("#NOWDATE#", date("Ymd"), LOG_PATH);
			// ログファイルを開く
			$logFp = fopen($fpathStr, "a+");
			// ログ書き込み
			$lineStr  = date("Y/m/d H:i:s") . " ";
			$lineStr .= $this->logLevelStr[$logLevel] . " ";
			$lineStr .= $textStr;
			fwrite($logFp, $lineStr . "\n");
			// ファイルクローズ
			fclose($logFp);
		}

		return true;
	}

	/*
	 *  リクエストパラメーター取得
	 */
	function getParams()
	{
		// POST
		if($_SERVER["REQUEST_METHOD"] == "POST")
		{
			$this->requestType = "POST";
			$this->params = $_POST;
		}
		// GET
		else
		{
			$this->params = $_GET;
		}
	}

	/*
	 *  文字置換
	 */
	function encodeHtmlStr($text, $lfRepl = false)
	{
		// XSS対策
		$bufStr = str_replace("&", "&amp;", $text);
		$bufStr = str_replace('\\"', '&quot;', $bufStr);
		$bufStr = str_replace("\\'", "&#039;", $bufStr);
		$bufStr = str_replace("<", "&lt;", $bufStr);
		$bufStr = str_replace(">", "&gt;", $bufStr);
		$bufStr = str_replace("\\\\", "￥", $bufStr);
		// 改行コードを<br>に変更
		if($lfRepl)
			$bufStr = str_replace("\r\n", "<br>", $bufStr);

		return $bufStr;
	}

	function decodeHtmlStr($text)
	{
		// <br>を改行コードに変更
		$bufStr = str_replace("<br>", "\r\n", $text);

		return $bufStr;
	}

	/*
	 *  エラー情報設定
	 */
	function setErrorInfo($errMsg, $dbErrMsg = null, $dbErrCmd = null, $dbErrPrm = null)
	{
		// エラー定義
		$this->isErr = true;
		$this->errMsg = $errMsg;
		if($dbErrMsg != null) $this->dbErrMsg = $dbErrMsg;
		if($dbErrCmd != null) $this->dbErrCmd = $dbErrCmd;
		if($dbErrPrm != null)  $this->dbErrQuery = print_r($dbErrPrm, true);
		// ログ出力
		$this->logWrite(SP_LOG_ERROR, $this->errMsg);
	}

	/*
	 *  DBコネクション取得
	 */
	function getConnection($dbName, $dbSrv = null, $dbUser = null, $dbPass = null)
	{
		$conn = null;

		// 管理DBの場合
		if($dbName === ADMINDBNAME)
		{
			$dbSrv = DBSERVER;
			$dbUser = BASEDBUSER;
			$dbPass = BASEDBPASS;
		}
		// それ以外の場合、パラメーター不備ならエラー判定
		else if(strlen($dbName) < 1 || strlen($dbSrv) < 1 || strlen($dbUser) < 1 || strlen($dbPass) < 1)
		{
			// エラー設定
			$this->setErrorInfo(
				"DBコネクションパラメーターエラー[".$dbName.":".$dbSrv.":".$dbUser.":".$dbPass."]"
			);
			throw new BaseModuleException($this->errMsg);
			return null;
		}

		try
		{
			// DB接続設定
			$connStr = "mysql:host=".$dbSrv.";dbname=".$dbName;
			// 日本語設定（これがないと日本語文字化けする）
			$connOpt = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
			// DBサーバーへコネクト
			$conn = new PDO($connStr, $dbUser, $dbPass, $connOpt);
			// オートコミットをオフにする
			$conn->query('SET AUTOCOMMIT = 0');
		}
		catch(PDOException $pdoe)
		{
			$this->setErrorInfo("DB接続エラー", $pdoe->getMessage(), "");
			throw new BaseModuleException($this->errMsg);
		}
		catch(Exception $e)
		{
			$this->setErrorInfo("例外（Exception）:[".$e->getMessage()."]");
			throw new BaseModuleException($this->errMsg);
		}

		return $conn;
	}

	/*
	 *  SQL実行（SELECT）
	 */
	function queryExec($conn, $sql, $condArray = null)
	{
		$result = false;

		try
		{
			if(is_array($condArray) && count($condArray) > 0)
			{
				$sth = $conn->prepare($sql);
				$sth->execute($condArray);
				$result = $sth;
			}
			else
			{
				$result = $conn->query($sql);
			}
		}
		catch(PDOException $pdoe)
		{
			$this->setErrorInfo("SQL実行エラー", $pdoe->getMessage(), $sql);
			throw new BaseModuleException($this->errMsg);
		}
		catch(Exception $e)
		{
			$this->setErrorInfo("例外（Exception）:[".$e->getMessage()."]");
			throw new BaseModuleException($this->errMsg);
		}

		return $result;
	}

	/*
	 *  SQL実行（INSERT/UPDATE/DELETE）
	 */
	function actionExec($conn, $sql, $prm = null)
	{
		$result = false;

		try
		{
			// SQL実行
			$stmt = $conn->prepare($sql);
			$result = $stmt->execute($prm);
		}
		catch(PDOException $pdoe)
		{
			$this->setErrorInfo("SQL実行エラー", $pdoe->getMessage(), $sql, $prm);
			throw new BaseModuleException($this->errMsg);
		}
		catch(Exception $e)
		{
			$this->setErrorInfo("例外（Exception）:[".$e->getMessage()."]");
			throw new BaseModuleException($this->errMsg);
		}

		return $result;
	}

	/*
	 *  トランザクション開始
	 */
	function beginExec($conn)
	{
		$result = false;

		try
		{
			$conn->beginTransaction();
		}
		catch(PDOException $pdoe)
		{
			$this->setErrorInfo("SQL実行エラー", $pdoe->getMessage(), $sql);
			throw new BaseModuleException($this->errMsg);
		}
		catch(Exception $e)
		{
			$this->setErrorInfo("例外（Exception）:[".$e->getMessage()."]");
			throw new BaseModuleException($this->errMsg);
		}

		return $result;
	}

	/*
	 *  ロールバック
	 */
	function rollbackExec($conn)
	{
		$result = false;

		try
		{
			$conn->rollBack();
		}
		catch(PDOException $pdoe)
		{
			$this->setErrorInfo("SQL実行エラー", $pdoe->getMessage(), $sql);
			throw new BaseModuleException($this->errMsg);
		}
		catch(Exception $e)
		{
			$this->setErrorInfo("例外（Exception）:[".$e->getMessage()."]");
			throw new BaseModuleException($this->errMsg);
		}

		return $result;
	}

	/*
	 *  コミット
	 */
	function commitExec($conn)
	{
		$result = false;

		try
		{
			$conn->commit();
		}
		catch(PDOException $pdoe)
		{
			$this->setErrorInfo("SQL実行エラー", $pdoe->getMessage(), $sql);
			throw new BaseModuleException($this->errMsg);
		}
		catch(Exception $e)
		{
			$this->setErrorInfo("例外（Exception）:[".$e->getMessage()."]");
			throw new BaseModuleException($this->errMsg);
		}

		return $result;
	}

	/*
	 *  テーブルから項目取得し、SELECT文実行
	 */
	function createSelectSql($tbName, $orderBy = null, $cond = array())
	{
		$whereArray = array();

		// SQL文を発行
		$sql = "SELECT * FROM ".$tbName;
		if(count($cond) > 0)
		{
			foreach($cond as $whereCol => $whereVal)
			{
				$whereArray[] = $whereCol." = '".$whereVal."'";
			}
			$sql .= " WHERE ".join($whereArray, " AND ");
		}
		if($orderBy != null)
		{
			$sql .= " ORDER BY ".$orderBy;
		}

		return $sql;
	}

	/*
	 *  SQLの実行結果から項目名を取得
	 */
	function getColumnInfo($result)
	{
		$colInfo = array();
		// 項目名のみ取得
		$cols = $result->fetch(PDO::FETCH_ASSOC);
		foreach ($cols as $colName => $colVal)
		{
			if($colName != "insert_timestamp" && $colName != "update_timestamp" && $colName != "status")
				$colInfo[] = $colName;
		}

		return $colInfo;
	}

	/*
	 *  テーブル名と配列からINSERT文組み立て
	 */
	function createInsertSql($tbName, $params)
	{
		$columns = array();
		$dataVal = array();
		// 取得したパラメータの扱い
		foreach($params as $sqlKey => $sqlVal)
		{
			if($sqlKey != "workflag" && $sqlKey != "set_btn")
			{
				$columns[] = $sqlKey;
				$dataVal[] = $this->encodeHtmlStr($sqlVal, true);
			}
		}
		// SQL文の組み立て
		$sql  = "INSERT INTO ".$tbName." (";
		$sql .= join($columns, ",").",insert_timestamp,update_timestamp,status";
		$sql .= ") VALUES ('";
		$sql .= join($dataVal, "','");
		$sql .= "',now(),now(),0)";

		return $sql;
	}

	/*
	 *  テーブル名と配列とキーからUPDATE文組み立て
	 */
	function createUpdateSql($tbName, $params, $whereKey)
	{
		$setStr = array();
		// 取得したパラメータの扱い
		foreach($params as $sqlKey => $sqlVal)
		{
			if($sqlKey != "workflag" && $sqlKey != "set_btn" && !strstr($whereKey, $sqlKey))
			{
				$setStr[] = $sqlKey." = '".$this->encodeHtmlStr($sqlVal, true)."'";
			}
		}
		// SQL文の組み立て
		$sql  = "UPDATE ".$tbName." SET ";
		$sql .= join($setStr, ",");
		$sql .= ", update_timestamp = now() ";
		$sql .= "WHERE ".$whereKey;

		return $sql;
	}

	/*
	 *  テーブル名と配列とキーからUPDATE文組み立て
	 */
	function createDeleteSql($tbName, $whereKey)
	{
		$setStr = array();
		// SQL文の組み立て
		$sql  = "DELETE FROM ".$tbName." ";
		$sql .= "WHERE ".$whereKey;

		return $sql;
	}
}
?>
