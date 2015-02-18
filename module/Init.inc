<?php
/*
 *  各種設定
 */

// 動作関連設定
define("DEBUGMODE", false);				// PHPのエラーを画面表示するかどうか

// DB関連設定
define("DBSERVER", "mysql318.db.sakura.ne.jp");		// DBサーバー
//define("DBSERVER", "localhost");		// DBサーバー
define("BASEDBUSER", "code4kashiwa");			// DBアクセスユーザーID
//define("BASEDBUSER", "root");			// DBアクセスユーザーID
define("BASEDBPASS", "c4k0208snow");			// DBアクセスパスワード
//define("BASEDBPASS", "");			// DBアクセスパスワード
define("ADMINDBNAME", "code4kashiwa_iodd2015");		// 管理DB名

// テーブル関連情報（StamPlace標準）
define("TBL_ADMIN", "admin");				// 管理者ユーザ情報
define("TBL_PLACE", "place");				// 基本位置情報
define("TBL_PLACE_ADVANCE", "place_advance");		// 追加位置情報
define("TBL_PLACE_TIME", "place_time");			// 位置別時間情報
// テーブル関連情報（アプリ固有）
define("TBL_STORE_USER", "store_user");			// 直売所登録担当ユーザ情報

// リクエストパラメータ情報
define("PRM_API_KEY", "apikey");		   	// APIキー
define("PRM_RESTYPE", "res");				// レスポンス種別（json, jsonp, xml）
define("PRM_RESENC", "enc");				// 文字エンコード（jis, sjis, euc, utf8）
define("PLACE_ID", "pid");		    		// 場所コード

// レスポンスパラメータ情報
define("RES_STATUS", "result");			   	// レスポンスステータス
define("STATUS_OK", 0);				   	// OK（レスポンスステータス）
define("STATUS_NG", 1);				   	// NG（レスポンスステータス）
define("RES_ERROR", "error");			   	// エラー情報
define("RES_DATA", "data");			   	// 出力データ

// 表示ステータス情報
define("VIEW_NG", 0);			 	  	// 表示NG（仮登録）
define("VIEW_OK", 1);				   	// 表示OK
define("VIEW_INIT", VIEW_OK);			  	// マップ上からの登録時に設定されるステータス

// ログ関連設定
define("LOG_PATH", "/home/code4kashiwa/log/iodd2015_api_log.#NOWDATE#.log");	// ログファイル格納場所
//define("LOG_PATH", "C:\\xampp\\htdocs\\stamplace\\module\\api_log.#NOWDATE#.log");	// ログファイル格納場所
define("SP_LOG_DEBUG", 0);				// ログレベル（デバッグ）
define("SP_LOG_INFO", 1);				// ログレベル（テキスト）
define("SP_LOG_WARNING", 2);				// ログレベル（警告以上）
define("SP_LOG_ERROR", 4);				// ログレベル（通常エラー）
define("SP_LOG_CRITICAL", 8);				// ログレベル（致命的エラー）
define("SP_LOG_ALL", -1);				// ログレベル（全部表示）
define("SP_LOG_NONE", 99999999);			// ログレベル（表示しない）





// メール関連設定
define("ADMINMAILADDRESS", "cqn00543@nifty.com");	// Fromメールアドレス

// ディレクトリ・ファイル関連設定
define("IMAGEBASEDIR", "image/");			// 画像格納ディレクトリ
define("TMPLBASEDIR", "template/");			// フォームテンプレート格納ディレクトリ
define("INQBASEDIR", "/inquiry/");			// お問い合わせフォーム格納ディレクトリ

// 機能設定
define("APPENTRYINDEX", "entry.index");			// 初期登録情報入力画面
define("APPLOGININDEX", "index.login");			// ログイン画面
define("APPMENUINDEX", "index.menu");			// メニュー画面
define("APPNEWSINDEX", "news.index");			// ニュース画面

?>
