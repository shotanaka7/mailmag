/**
 * メール送信画面用jQuery
 * @since 1.0.0
 */
jQuery(function() {
	// 予約、即時送信でのフォームの表示非表示切り替え
	jQuery('input[name="mailmag_timing"]').change(function(){ // タイミングのラジオボタンが変更されたら
		var radioval = jQuery(this).val(); // 選択されているボタンのvalueを取得
		if(radioval == 1){ // valueが1なら
			jQuery('#mailmag-time-select-wrapper').addClass("mailmag-display-none"); // 予約時間のボックスを日表示するためのクラスを与える
		}else{ // そうでなければ
			jQuery('#mailmag-time-select-wrapper').removeClass("mailmag-display-none"); // 予約時間のボックスを日表示するためのクラスを削除
		}
	});

	// ページ読み込み時の年月の取得と日付の出力
	var y = jQuery('#send-year').val(); // yearの初期値を取得
	var m = jQuery('#send-month').val(); // monthの初期値を取得
	var d = new Date(y, m, 0).getDate(); // 取得した年月から最終日を計算
	var today = new Date(); // 今日の日付を取得
	var today_day = today.getDate(); // 今日の'日'だけ抜き出す
	day = 1; // 日付の開始日を設定
	for( var i = 1; i <= d; i++ ){ // 最終日の数までループ
		jQuery('#send-day').append(jQuery('<option>').html(day).val(day)); // optionを出力
		day ++;
	}
	jQuery('#send-day option[value=' + today_day + ']').attr('selected','true'); // ページ読み込み時に今日の'日'を選択しておく

	// 年が変更された時に年月の取得と日付の出力
	jQuery('#send-year').change(function(){
		jQuery('#send-day > option').remove(); // '日'を再出力する前にoptionを全て削除
		var y = jQuery('#send-year').val(); // yearの値を取得
		var m = jQuery('#send-month').val(); // monthの値を取得
		var d = new Date(y, m, 0).getDate(); // 取得した年月から最終日を計算
		day = 1; //日付の開始日を再度セット
		for( var i = 1; i <= d; i++ ){ // 最終日の数までループ
			jQuery('#send-day').append(jQuery('<option>').html(day).val(day)); // optionを出力
			day ++; // 1日足す
		}
	});

	// 月が変更された時に年月の取得と日付の出力
	jQuery('#send-month').change(function(){
		jQuery('#send-day > option').remove(); // '日'を再出力する前にoptionを全て削除
		var y = jQuery('#send-year').val(); // yearの値を取得
		var m = jQuery('#send-month').val(); // monthの値を取得
		var d = new Date(y, m, 0).getDate(); // 取得した年月から最終日を計算
		day = 1; //日付の開始日を再度セット
		for( var i = 1; i <= d; i++ ){ // 最終日の数までループ
			jQuery('#send-day').append(jQuery('<option>').html(day).val(day)); //optionを出力
			day ++; // 1日足す
		}
	});
});
