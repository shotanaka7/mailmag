<?php
/**
 * フォームから情報を受け取る
 **/
$format           = $_POST['format']; // メール送信形式
$subject          = $_POST['subject']; // 件名
$message          = $_POST['message']; // メッセージ
$timing           = $_POST['mailmag_timing']; // 送信のタイミング
$mail_group       = $_POST['mail_group']; // 送信グループ
$send_time_year   = $_POST['send_year']; // 送信日：年
$send_time_month  = $_POST['send_month']; // 送信日：月
$send_time_day    = $_POST['send_day']; // 送信日：日
$send_time_hour   = $_POST['send_hour']; // 送信日：月
$send_time_minute = $_POST['send_minute']; // 送信日：日

mailmag_sent_mail( $format, $subject, $message, $timing, $mail_group, $send_time_year, $send_time_month, $send_time_day, $send_time_hour, $send_time_minute );

function mailmag_sent_mail( $format, $subject, $message, $timing, $mail_group, $send_time_year, $send_time_month, $send_time_day, $send_time_hour, $send_time_minute ) {
	require_once( dirname( __FILE__ ) . '/get-db.php' );
	require_once( dirname( __FILE__ ) . '/get-unixtime-millsecond.php' );

	// 送信直前の情報登録
	global $wpdb;
	$mail_id = mailmag_get_id(); // データベースから次のテーブルIDを取得
	$user    = wp_get_current_user(); // ログインユーザーを取得
	$author  = $user->ID; // ログインユーザーのIDを取得

	// 変数$send_timeに値を格納しておく
	date_default_timezone_set( 'Asia/Tokyo' );
	if ( 1 == $timing ) { // 即時配信だったら
		$send_time = date( 'Y-m-d H:i:s' ); // 現在時間を入れる
		$timestamp = time();
	} elseif ( 2 == $timing ) { // 予約配信だったら
		// 受け取った日時を入れる
		$send_time = $send_time_year . '-' . sprintf( '%02d', $send_time_month ) . '-' . sprintf( '%02d', $send_time_day ) . ' ' . sprintf( '%02d', $send_time_hour ) . ':' . sprintf( '%02d', $send_time_minute ) . ':' . '00';
		// さらにその時間をTokyo->UTC->unixtimeに変換して変数にセット
		$t         = new DateTime( $send_time );
		$t         = $t->format( 'Y-m-d H:i:s' );
		$timestamp = strtotime( $t );
	} else {
		// WP_Errorのセット
		$e = new WP_Error();
		$e->add( 'error', 'mailmag error : $send_timeの値が正しくありません' );
		set_transient( 'mailmag-admin-errors', $e->get_error_messages(), 1 );
		wp_redirect( home_url() . '/wp-admin/admin.php?page=mailmag-sent-items&sent_mail=error' );
	}

	// 先に入れられる情報をデータベースに入れる
	$result = $wpdb->insert(
		$wpdb->prefix . 'mailmag_sent_message', array(
			'id'                   => $mail_id,
			'time'                 => current_time( 'mysql' ),
			'unix_timestamp_start' => get_unixtime_millsecond(),
			'author'               => $author,
			'mail_group'           => $mail_group,
			'status'               => 99,
			'timing'               => $timing,
			'send_time'            => $send_time,
			'send_time_timestamp'  => $timestamp,
			'format'               => $format,
			'subject'              => $subject,
			'message'              => str_replace( array( "\r\n", "\n", "\r" ), '\n', $message ),
		)
	);

	if ( false === $result ) {
		wp_redirect( home_url() . '/wp-admin/admin.php?page=mailmag-sent-items&sent_mail=0' );
		// WP_Errorのセット
		$e = new WP_Error();
		$e->add( 'error', 'mailmag error : DBに行が挿入できません' );
		set_transient( 'mailmag-admin-errors', $e->get_error_messages(), 1 );
	} else {
		// 即時か予約で処理を分岐
		if ( 1 == $timing ) { // 即時送信だったら
			mailmag_sent_function( $mail_id ); // メール送信functionを実行
			// WP_Errorのセット
			$e = new WP_Error();
			$e->add( 'updated', 'mailmag : メールを送信しました' );
			set_transient( 'mailmag-admin-updates', $e->get_error_messages(), 1 );
			wp_redirect( home_url() . '/wp-admin/admin.php?page=mailmag-sent-items&sent_mail=1' );
		} elseif ( 2 == $timing ) { // 予約送信だったら
			wp_schedule_single_event( $timestamp, MAILMAG_CRON_HOOK, array( $mail_id ) ); // wp-cronに一度きりのイベントをセット
			// ステータスを書き換え
			$mailmag_sent_message_update_data = array(
				'status' => 2,
			);
			$where                            = array(
				'id' => $mail_id,
			);
			$format                           = array(
				'%d',
			);
			$where_format                     = array( '%d' );
			$wpdb->update( $wpdb->prefix . 'mailmag_sent_message', $mailmag_sent_message_update_data, $where, $format, $where_format );
			// WP_Errorのセット
			$e = new WP_Error();
			$e->add( 'updated', 'mailmag : メールの送信予約をセットしました' );
			set_transient( 'mailmag-admin-updates', $e->get_error_messages(), 1 );
			wp_redirect( home_url() . '/wp-admin/admin.php?page=mailmag-sent-items&sent_mail=2' );
		}
	}// End if().
}
