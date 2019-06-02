<?php

/* データベース構造のバージョン番号をグローバル変数に格納 */
global $mailmag_db_version;
$mailmag_db_version = MAILMAG_DB_VERSION;

/* DBテーブルの作成 */
function mailmag_create_table()
{
    global $mailmag_db_version;
    mailmag_create_mailmag_sent_message($mailmag_db_version); // message詳細用
    mailmag_create_mailmag_sent_log($mailmag_db_version); // 送信ログ用
    mailmag_create_mailmag_admin_users($mailmag_db_version); // 開発者のメールアドレス保存用
}

// mailmag_sent_messageテーブル作成function
function mailmag_create_mailmag_sent_message( $mailmag_db_version )
{
    global $wpdb;
    /* mailmag_sent_messageテーブルがあるか確認する */
    $table_name = $wpdb->prefix . 'mailmag_sent_message';
    /* テーブルがなければ追加 */
    $charset_collate = $wpdb->get_charset_collate(); // テーブルのデフォルト文字セットと照合順序を指定

    /**
     * テーブル構造
     *
     * UNIXタイムスタンプ 送信開始時間
     * 作成者
     * 送信グループ
     * 送信ステータス
     * 送信件数
     * 送信成功件数
     * 送信失敗件数
     * 送信のタイミング
     * 送信予定時間(タイミングが予約の場合のみ日時を入力)
     * メール形式(HTML,Text)
     * 件名
     * 本文
     **/
    $sql = "CREATE TABLE if not exists $table_name( id int primary key auto_increment,";
    $sql .= " time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,";
    $sql .= ' unix_timestamp_start tinytext NOT NULL,';
    $sql .= ' author tinytext NOT NULL,';
    $sql .= ' mail_group tinytext NOT NULL,';
    $sql .= ' status int,';
    $sql .= ' number_send int,';
    $sql .= ' sent_count int,';
    $sql .= ' error_count int,';
    $sql .= ' timing tinytext NOT NULL,';
    $sql .= " send_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,";
    $sql .= ' send_time_timestamp int,';
    $sql .= ' format tinytext NOT NULL,';
    $sql .= ' subject tinytext NOT NULL,';
    $sql .= ' message text NOT NULL,';
    $sql .= ' UNIQUE KEY id(id)';
    $sql .= " ) $charset_collate";

    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
    // DBのバージョン番号を記録させておく
    add_option('mailmag_db_version', $mailmag_db_version);
}

// mailmag_sent_logテーブル作成function
function mailmag_create_mailmag_sent_log( $mailmag_db_version )
{
    global $wpdb;
    /* mailmag_sent_logテーブルがあるか確認する */
    $table_name = $wpdb->prefix . 'mailmag_sent_log';
    /* テーブルがなければ追加 */
    $charset_collate = $wpdb->get_charset_collate(); // テーブルのデフォルト文字セットと照合順序を指定

    /**
     * テーブル構造
     *
     * UNIXタイムスタンプ
     * メールのID
     * 送信の成否判定
     * メールアドレス
     **/
    $sql = "CREATE TABLE if not exists $table_name( id int primary key auto_increment,";
    $sql .= " time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,";
    $sql .= ' unix_timestamp tinytext NOT NULL,';
    $sql .= ' mail_id int,';
    $sql .= ' success tinyint(1),';
    $sql .= ' email tinytext NOT NULL,';
    $sql .= ' UNIQUE KEY id(id)';
    $sql .= " ) $charset_collate";

    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
    add_option('mailmag_db_version', $mailmag_db_version); // DBのバージョン番号を記録させておく
}

// mailmag_admin_usersテーブル作成function
function mailmag_create_mailmag_admin_users( $mailmag_db_version )
{
    global $wpdb;
    /* mailmag_sent_logテーブルがあるか確認する */
    $table_name = $wpdb->prefix . 'mailmag_admin_users';
    /* テーブルがなければ追加 */
    $charset_collate = $wpdb->get_charset_collate(); // テーブルのデフォルト文字セットと照合順序を指定

    /**
     * テーブル構造
     *
     * メールアドレス
     **/
    $sql = "CREATE TABLE if not exists $table_name( id int primary key auto_increment,";
    $sql .= " time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,";
    $sql .= ' email tinytext NOT NULL,';
    $sql .= ' UNIQUE KEY id(id)';
    $sql .= " ) $charset_collate";

    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
    add_option('mailmag_db_version', $mailmag_db_version); // DBのバージョン番号を記録させておく
}
