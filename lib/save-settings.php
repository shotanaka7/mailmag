<?php
// フォームから情報を受け取り
$emails = $_POST['mailmag_admin_emails'];
// functionに受け取った情報を渡して実行
mailmag_set_admin_users( $emails );

function mailmag_set_admin_users( $emails ) {
	include_once dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php'; // wp-load.phpを読み込み
	global $wpdb;
	$table_name = 'mailmag_admin_users';
	$prep_sql   = 'SELECT email FROM ' . $wpdb->prefix . $table_name . ' ORDER BY email ASC;';
	// @codingStandardsIgnoreStart
	$results = $wpdb->get_results( $prep_sql );
	// @codingStandardsIgnoreEnd
	// テーブルにデータが一件でもあれば
	if ( 0 != count( $results ) ) {
		$table_name = $wpdb->prefix . 'mailmag_admin_users';
		$sql        = "TRUNCATE $table_name";
		// テーブルのデータを削除
     // @codingStandardsIgnoreStart
     $query = $wpdb->query( $wpdb->prepare( $sql ) );
     // @codingStandardsIgnoreEnd
	}
	// emailsを分解
	if ( ! empty( $emails ) ) { // もし$emailsにメールアドレスが入っていれば
		$emails = explode( ',', $emails ); // カンマ区切りの文字列を配列に変換
		foreach ( $emails as $email ) { // 配列の中身を一つずつDBに登録
			$wpdb->insert(
				$wpdb->prefix . 'mailmag_admin_users', array(
					'time'  => current_time( 'mysql' ),
					'email' => $email,
				)
			);
		}
	}
	$save = 1; // 保存処理の結果をsaveに入れる
	if ( 1 == $save ) { // 上の処理が全て終わったら
		wp_redirect( home_url() . '/wp-admin/admin.php?page=mailmag-settings&save=1' ); // リダイレクトさせてgetで値を渡し
		exit; // 処理を終了
	}
}
