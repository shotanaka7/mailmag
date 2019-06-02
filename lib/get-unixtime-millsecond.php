<?php
function get_unixtime_millsecond() {
	//microtimeを.で分割
	$arr_time = explode( '.', microtime( true ) );
	//日時＋ミリ秒
	return date( 'Y-m-d H:i:s', $arr_time[0] ) . '.' . $arr_time[1];
}
