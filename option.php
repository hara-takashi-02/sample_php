<?php
// *******************************************************************
// 追加設定ファイル
// *******************************************************************


// *******************************************
// Chromeのコンソールにvar_dumpする関数
// *******************************************
function console ( $d ) {
	// var_dump を変数に入れる
	ob_start();
	var_dump($d);
	$dump = ob_get_contents();
	ob_end_clean();
  
	// 文字列をサニタイズとかconsole.logで見やすいように
	$dump = str_replace(array("rn","r","n"), 'n', $dump);
	$dump = str_replace("'", '"', $dump);

	$dump = str_replace("\n", "", $dump);
  
	// scriptタグとconsole.logを出力
	$str = "<script>\n";
	$str .= "console.log('" . $dump. "');\n";
	$str .= "</script>\n";
	echo $str;
	return;
  }

// *******************************************
// ルームリスト読み込み
// *******************************************
function ladiesRoom_Read(){

	$status = 0;
	$cnn = new mysqli(CMN_HOST, CMN_USER, CMN_PASS, CMN_DATB, CMN_PORT);
	$cnn->set_charset("utf8");
	$stmt = $cnn->stmt_init();
	$sql  = 

	$sql  = 
'SELECT '
	.'room_id,
	room_name'.
' FROM '
	.'room_data'.
' WHERE '
	.'status = ?'.
' ORDER BY '
	.'room_id';

	if($stmt->prepare($sql)) {

		$stmt->bind_param("i", $status);
		$stmt->execute();
		$stmt->store_result();
		$cnt = $stmt->num_rows;
		if($cnt > 0){
			$stmt->bind_result(
				$id,
				$name
			);
			while ($stmt->fetch()) {
				$room[$id] = $name;
			}
			$stmt->close();
		}
	}
	$cnn->close();
	return $room;

}
// *******************************************
// オプション情報読み込み
// *******************************************
function option_Read(){

	$cnn = new mysqli(CMN_HOST, CMN_USER, CMN_PASS, CMN_DATB, CMN_PORT);
	$cnn->set_charset("utf8");

	$sql  = 
'SELECT '
	.'option_room,option_twitter,option_town,option_movie,option_week,option_display_esthe,regist_dt,update_dt'.
' FROM '
	.'option_data'.
' WHERE '
	.'option_id = 1'.
' LIMIT '
	.'0, 1';

	$stmt = $cnn->prepare($sql);
	$stmt->execute();
	$stmt->bind_result(
		$option_room,//0
		$option_twitter,
		$option_town,
		$option_movie,
		$option_week,
		$option_display_esthe,
		$regist_dt,
		$update_dt
	);
	$stmt->fetch();
	$stmt->close();

	$regist = new DateTime( $regist_dt );
	$update = new DateTime( $update_dt );

	$arr = array(
		$option_room,
		$option_twitter,
		$option_town,
		$option_movie,
		$option_week,
		$option_display_esthe,
		$regist->format('Y/m/d H:i'),
		$update->format('Y/m/d H:i')
	);
	return $arr;

}
//全ファイル共通
$common_option_Read = option_Read();
$common_option_room = $common_option_Read[0];
$common_option_twitter = $common_option_Read[1];
$common_option_town = $common_option_Read[2];
$common_option_movie = $common_option_Read[3];
$common_option_week = $common_option_Read[4];
$common_option_display_esthe = $common_option_Read[5];
$common_option_display_esthe = explode("/",$common_option_display_esthe);
$common_op_tetris = $common_option_display_esthe[0];
$common_op_topladys = $common_option_display_esthe[1];

// *******************************************
// 登録女性画像読み込み
// *******************************************
function photoData_All_FileExt( $lid , $view){
	//$viewは管理画面が0、サイト表が1に設定

	$cnn = new mysqli(CMN_HOST, CMN_USER, CMN_PASS, CMN_DATB, CMN_PORT);
	$cnn->set_charset("utf8");
	$stmt = $cnn->stmt_init();
	$sql  = 
'SELECT '
	.'imgcatg_id, imgcatg_name'.
' FROM '
	.'image_category'.
' WHERE '
	.'status = 0';

	if($stmt->prepare($sql)) {

		$stmt->execute();
		$stmt->store_result();
		$cnt = $stmt->num_rows;
		if($cnt > 0){
			$stmt->bind_result(
				$imgcatg_id,
				$imgcatg_name
			);
			$catg_arr = array();
			while ($stmt->fetch()) {
				array_push($catg_arr, $imgcatg_id);
			}
			$stmt->close();
		}
	}
	$js = "$(function(){\n";
	for($i=0; $i<count($catg_arr); $i++){

		$stmt2 = $cnn->stmt_init();
		$sql2  = 
'SELECT '
	.'imgsize_id, imgsize_name, imgsize_width, imgsize_height, imgsize_prefix'.
' FROM '
	.'image_size'.
' WHERE '
	.'status = 0'.
' AND '
	.'imgsize_root = 0'.
' AND '
	.'imgsize_catg = ?';

		if($stmt2->prepare($sql2)) {

			$stmt2->bind_param("i", $catg_arr[$i]);
			$stmt2->execute();
			$stmt2->store_result();
			$cnt2 = $stmt2->num_rows;
			if($cnt2 > 0){
				$stmt2->bind_result(
					$imgsize_id,
					$imgsize_name,
					$imgsize_width,
					$imgsize_height,
					$imgsize_prefix
				);

				while ($stmt2->fetch()) {
					$oya_arr = photoOya_Read( $lid, $imgsize_id );
					$ext = getFileExtension( $oya_arr[1] );
					
					if(!$ext || $ext == "jpeg" && $view == 0){
						$extension[] = "jpg";
					}else{
						$extension[] = getFileExtension( $oya_arr[1] );
					}
				}
				$stmt2->close();
			}
		}
	}
	$js .= "});\n";
	
	return $extension;

}

// *******************************************
// 店舗基本情報読み込み
// *******************************************
function profileInfo_Read_NEW(){

	$cnn = new mysqli(CMN_HOST, CMN_USER, CMN_PASS, CMN_DATB, CMN_PORT);
	$cnn->set_charset("utf8");

	$sql  = 
'SELECT '
	.'profile_shpname,profile_shpurl,profile_shptel,profile_jobtel,profile_shpaddr,profile_start,profile_end,profile_smtpaddr,profile_smtphost,profile_smtpport,profile_smtpuser,profile_smtppass,profile_cmnaddr,profile_rsvaddr,profile_jobaddr,profile_stfaddr,regist_dt,update_dt,profile_smtpauth,profile_mbmail1,profile_mbmail2,profile_ekiid,profile_ekipass,shop_autonum,profile_twitter_id,profile_lineurl,profile_lineurl2,profile_finish_text,profile_widget,profile_tiktok,profile_insta,profile_secretkey,profile_sitekey,profile_memo'.
' FROM '
	.'profile_data'.
' WHERE '
	.'profile_id = 1'.
' LIMIT '
	.'0, 1';

	$stmt = $cnn->prepare($sql);
	$stmt->execute();
	$stmt->bind_result(
		$profile_shpname,//0
		$profile_shpurl,
		$profile_shptel,
		$profile_jobtel,
		$profile_shpaddr,
		$profile_start,//5
		$profile_end,
		$profile_smtpaddr,//送信メールアドレス
		$profile_smtphost,//SMTPサーバ
		$profile_smtpport,//ポート番号
		$profile_smtpuser,//10 ユーザID
		$aes_pass,//パスワード
		$profile_cmnaddr,
		$profile_rsvaddr,
		$profile_jobaddr,
		$profile_stfaddr,//15
		$regist_dt,
		$update_dt,
		$profile_smtpauth,//SMTP認証
		$profile_mbmail1,
		$profile_mbmail2,//20
		$profile_ekiid,
		$profile_ekipass,
		$shop_autonum,
		$profile_twitter_id,
		$profile_lineurl,//25
		$profile_lineurl2,
		$profile_finish_text,
		$profile_widget,
		$profile_tiktok,
		$profile_insta,//30
		$profile_secretkey,
		$profile_sitekey,
		$profile_memo
	);
	$stmt->fetch();
	$stmt->close();

	$start = new DateTime( $profile_start );
	$end = new DateTime( $profile_end );
	$regist = new DateTime( $regist_dt );
	$update = new DateTime( $update_dt );
	if($aes_pass){
		$enc_pass  = base64_decode( $aes_pass );
		$blowfish = Crypt_Blowfish::factory( 'cbc', CBF_KEY, CBF_IV );
		$profile_smtppass = $blowfish->decrypt( $enc_pass );
		$profile_smtppass = rtrim( $profile_smtppass, "\0" );
		$aes_pass = "";
		$enc_pass = "";
		$blowfish = "";
	}
	$arr = array(
		stripslashes($profile_shpname),
		stripslashes($profile_shpurl),
		stripslashes($profile_shptel),
		stripslashes($profile_jobtel),
		stripslashes($profile_shpaddr),
		$start->format('H:i'),
		$end->format('H:i'),
		stripslashes($profile_smtpaddr),
		stripslashes($profile_smtphost),
		stripslashes($profile_smtpport),
		stripslashes($profile_smtpuser),
		$profile_smtppass,
		stripslashes($profile_cmnaddr),
		stripslashes($profile_rsvaddr),
		stripslashes($profile_jobaddr),
		stripslashes($profile_stfaddr),
		$regist->format('Y/m/d H:i'),
		$update->format('Y/m/d H:i'),
		$profile_smtpauth,
		stripslashes($profile_mbmail1),
		stripslashes($profile_mbmail2),
		stripslashes($profile_ekiid),
		stripslashes($profile_ekipass),
		stripslashes($shop_autonum),
		stripslashes($profile_twitter_id),
		stripslashes($profile_lineurl),
		stripslashes($profile_lineurl2),
		stripslashes($profile_finish_text),
		stripslashes($profile_widget),
		stripslashes($profile_tiktok),
		stripslashes($profile_insta),
		stripslashes($profile_secretkey),
		stripslashes($profile_sitekey),
		stripslashes($profile_memo)
	);
	return $arr;

}

// *******************************************
// ニュース登録(絵文字対応版)
// *******************************************
function newsBody_regist_custom( $title, $body ){

	$cnn = new mysqli(CMN_HOST, CMN_USER, CMN_PASS, CMN_DATB, CMN_PORT);
	$cnn->set_charset("utf8mb4");
	$sql = 
"INSERT INTO news_data ("
	."news_title,"
	."news_body,"
	."regist_dt,"
	."update_dt".
") VALUES ("
	."?,?,?,?".
")";
	$stmt = $cnn->prepare($sql);
	$stmt->bind_param('ssss',
		$title,
		$body,
		$now,
		$now
	);
	$now = date( "Y-m-d H:i:s" );
	$title = $cnn->real_escape_string($title);
	$body = $body;
	$stmt->execute();
	$new_id = $cnn->insert_id;
	$stmt->close();

	return $new_id;

}
// *******************************************
// ニュースデータ更新(絵文字対応版)
// *******************************************
function newsBody_Edit_custom( $arr ){

	$cnn = new mysqli(CMN_HOST, CMN_USER, CMN_PASS, CMN_DATB, CMN_PORT);
	$cnn->set_charset("utf8mb4");
	$sql = 
"UPDATE news_data SET ".
	"news_title = ?,".
	"news_body = ?,".
	"update_dt = ? ".
"WHERE news_id = ?";
	$stmt = $cnn->prepare($sql);
	$stmt->bind_param('sssi',
		$title,
		$body,
		$now,
		$id
	);
	$title = $cnn->real_escape_string($arr[1]);
	$body = $arr[2];
	$now = date( "Y-m-d H:i:s" );
	$id = $arr[0];
	$stmt->execute();
	$stmt->close();
	$cnn->close();

}
// *******************************************
// ニュース読み込み(絵文字対応版)
// *******************************************
function newsBody_Read_custom( $id ){

	$cnn = new mysqli(CMN_HOST, CMN_USER, CMN_PASS, CMN_DATB, CMN_PORT);
	$cnn->set_charset("utf8mb4");
	$stmt = $cnn->stmt_init();
	$sql  = 
'SELECT '
	.'news_title, news_body'.
' FROM '
	.'news_data'.
' WHERE '
	.'news_id = ?';

	if($stmt->prepare($sql)) {

		$stmt->bind_param("i", $id);
		$stmt->execute();
		$stmt->store_result();
		$cnt = $stmt->num_rows;
		if($cnt > 0){
			$stmt->bind_result(
				$title,
				$body
			);
			$stmt->fetch();
			$stmt->close();
		}
	}
	$arr = array( $title, $body );
	return $arr;

}

// *******************************************
// ニュース2登録
// *******************************************
function news2Body_regist_custom( $title, $body, $disp ){

	$cnn = new mysqli(CMN_HOST, CMN_USER, CMN_PASS, CMN_DATB, CMN_PORT);
	$cnn->set_charset("utf8mb4");
	$sql = 
"INSERT INTO news2_data ("
	."news2_title,"
	."news2_body,"
	."status,"
	."regist_dt,"
	."update_dt".
") VALUES ("
	."?,?,?,?,?".
")";
	$stmt = $cnn->prepare($sql);
	$stmt->bind_param('ssiss',
		$title,
		$body,
		$status,
		$now,
		$now
	);
	$now = date( "Y-m-d H:i:s" );
	$title = $cnn->real_escape_string($title);
	$body = $body;
	if($disp){
		$status = $disp;
	}else{
		$status = 0;
	}
	$stmt->execute();
	$new_id = $cnn->insert_id;
	$stmt->close();

	return $new_id;

}
// *******************************************
// ニュース2データ更新
// *******************************************
function news2Body_Edit_custom( $arr ){

	$cnn = new mysqli(CMN_HOST, CMN_USER, CMN_PASS, CMN_DATB, CMN_PORT);
	$cnn->set_charset("utf8mb4");
	$sql = 
"UPDATE news2_data SET ".
	"news2_title = ?,".
	"news2_body = ?,".
	"status = ?,".
	"update_dt = ? ".
"WHERE news2_id = ?";
	$stmt = $cnn->prepare($sql);
	$stmt->bind_param('ssisi',
		$title,
		$body,
		$status,
		$now,
		$id
	);
	$title = $cnn->real_escape_string($arr[1]);
	$body = $arr[2];
	$now = date( "Y-m-d H:i:s" );
	$id = $arr[0];
	$status = $arr[3];
	$stmt->execute();
	$stmt->close();
	$cnn->close();

}
// *******************************************
// ニュース2読み込み
// *******************************************
function news2Body_Read_custom( $id ){

	$cnn = new mysqli(CMN_HOST, CMN_USER, CMN_PASS, CMN_DATB, CMN_PORT);
	$cnn->set_charset("utf8mb4");
	$stmt = $cnn->stmt_init();
	$sql  = 
'SELECT '
	.'news2_title, news2_body,status'.
' FROM '
	.'news2_data'.
' WHERE '
	.'news2_id = ?';

	if($stmt->prepare($sql)) {

		$stmt->bind_param("i", $id);
		$stmt->execute();
		$stmt->store_result();
		$cnt = $stmt->num_rows;
		if($cnt > 0){
			$stmt->bind_result(
				$title,
				$body,
				$status
			);
			$stmt->fetch();
			$stmt->close();
		}
	}
	$arr = array( $title, $body, $status );
	return $arr;

}

// *******************************************
// 女性基本データ読み込み（カテ２入）
// *******************************************
function ladiesBasicdata2_Read_custom( $id, $view ){

	$cnn = new mysqli(CMN_HOST, CMN_USER, CMN_PASS, CMN_DATB, CMN_PORT);
	$cnn->set_charset("utf8mb4");
	$sql  = 
'SELECT '
	.'lady_category,lady_category2,lady_name,lady_kana,lady_age,lady_tall,lady_bust,lady_cup,lady_waist,lady_hip,lady_blood,lady_rookie,lady_exp,lady_hobby,lady_sales,lady_copypc,lady_copymb,lady_url,lady_urlttl,lady_smsg,lady_lmsg,lady_ekiid,entry_dt,status'.
' FROM '
	.'ladies_data'.
' WHERE '
	.'status <= ?'.
' AND '
	.'lady_id = ?'.
' LIMIT '
	.'0, 1';

	$stmt = $cnn->prepare($sql);
	$stmt->bind_param("ii", $view, $id);
	$stmt->execute();
	$stmt->bind_result(
		$lady_category,$lady_category2,$lady_name,$lady_kana,$lady_age,$lady_tall,//5
		$lady_bust,$lady_cup,$lady_waist,$lady_hip,$lady_blood,//10
		$lady_rookie,$lady_exp,$lady_hobby,$lady_sales,$lady_copypc,//15
		$lady_copymb,$lady_url,$lady_urlttl,$lady_smsg,$lady_lmsg,//20
		$lady_ekiid,$entry_dt,$status
	);
	while($stmt->fetch()) {
		$arr = array(
			$lady_category,$lady_category2,$lady_name,$lady_kana,$lady_age,$lady_tall,//5
			$lady_bust,$lady_cup,$lady_waist,$lady_hip,$lady_blood,//10
			$lady_rookie,$lady_exp,$lady_hobby,$lady_sales,$lady_copypc,//15
			$lady_copymb,$lady_url,$lady_urlttl,$lady_smsg,$lady_lmsg,//20
			$lady_ekiid,$entry_dt,$status
		);
	}
	$stmt->close();
	return $arr;

}

// *******************************************
// 女性基本データ登録（カテ２入）
// *******************************************
function ladiesData_Regist2_custom( $arr ){

	$cnn = new mysqli(CMN_HOST, CMN_USER, CMN_PASS, CMN_DATB, CMN_PORT);
	$cnn->set_charset("utf8mb4");
	$sql = 
"INSERT INTO ladies_data (".
	"lady_category,".
	"lady_category2,".
	"lady_name,".
	"lady_kana,".
	"lady_age,".
	"lady_tall,".
	"lady_bust,".
	"lady_cup,".
	"lady_waist,".
	"lady_hip,".
	"lady_blood,".
	"lady_rookie,".
	"lady_exp,".
	"lady_hobby,".
	"lady_sales,".
	"lady_url,".
	"lady_urlttl,".
	"lady_smsg,".
	"lady_lmsg,".
        "lady_ekiid,".
	"status,".
	"entry_dt,".
	"regist_dt,".
	"update_dt".
") VALUES ("
	."?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?".
")";
	$stmt = $cnn->prepare($sql);
	$stmt->bind_param('iissiiiiiiiiisssssssisss',
		$catg,
		$catg2,
		$name,
		$kana,
		$age,
		$tall,
		$bust,
		$cup,
		$waist,
		$hip,
		$blood,
		$rookie,
		$exp,
		$bobby,
		$sales,
		$url,
		$title,
		$msg,
		$lmsg,
                $ekiid,
		$status,
		$entry,
		$now,
		$now
	);
	$now = date( "Y-m-d H:i:s" );
	$catg = $arr[0];
	$catg2 = $arr[1];
	$name = $cnn->real_escape_string($arr[2]);
	$kana = $cnn->real_escape_string($arr[3]);
	$age = $arr[4];
	$tall = $arr[5];
	$bust = $arr[6];
	$cup = $arr[7];
	$waist = $arr[8];
	$hip = $arr[9];
	$blood = $arr[10];
	$rookie = $arr[11];
	$exp = $arr[12];
	$bobby = $cnn->real_escape_string($arr[13]);
	$sales = $cnn->real_escape_string($arr[14]);
	$url = $cnn->real_escape_string($arr[17]);
	$title = $cnn->real_escape_string($arr[16]);
	$msg = $arr[15];
	$lmsg = $arr[19];
        $ekiid = $arr[20];
	$status = 0;
	$entry = $arr[18];
	$stmt->execute();
	$id = $cnn->insert_id;
	$stmt->close();
	$cnn->close();

	return $id;

}

// *******************************************
// 女性基本データ更新（カテ２入）
// *******************************************
function ladiesData_Modify2_custom( $arr ){

	$cnn = new mysqli(CMN_HOST, CMN_USER, CMN_PASS, CMN_DATB, CMN_PORT);
	$cnn->set_charset("utf8mb4");
	$sql = 
"UPDATE ladies_data SET ".
	"lady_category = ?,".
	"lady_category2 = ?,".
	"lady_name = ?,".
	"lady_kana = ?,".
	"lady_age = ?,".
	"lady_tall = ?,".
	"lady_bust = ?,".
	"lady_cup = ?,".
	"lady_waist = ?,".
	"lady_hip = ?,".
	"lady_blood = ?,".
	"lady_rookie = ?,".
	"lady_exp = ?,".
	"lady_hobby = ?,".
	"lady_sales = ?,".
	"lady_url = ?,".
	"lady_urlttl = ?,".
	"lady_copypc = ?,".
	"lady_copymb = ?,".
	"lady_smsg = ?,".
	"lady_lmsg = ?,".
        "lady_ekiid = ?,".
	"status = ?,".
	"entry_dt = ?,".
	"update_dt = ? ".
"WHERE lady_id = ?";
	$stmt = $cnn->prepare($sql);
	$stmt->bind_param('iissiiiiiiiiisssssssssissi',
		$catg,
		$catg2,
		$name,
		$kana,
		$age,
		$tall,
		$bust,
		$cup,
		$waist,
		$hip,
		$blood,
		$rookie,
		$exp,
		$bobby,
		$sales,
		$url,
		$title,
		$pcopy,
		$mcopy,
		$msg,
		$lmsg,
                $ekiid,
		$status,
		$entry,
		$now,
		$lid
	);
	$catg = $arr[0];
	$catg2 = $arr[1];
	$name = $arr[2];
	$kana = $arr[3];
	$age = $arr[4];
	$tall = $arr[5];
	$bust = $arr[6];
	$cup = $arr[7];
	$waist = $arr[8];
	$hip = $arr[9];
	$blood = $arr[10];
	$rookie = $arr[11];
	$exp = $arr[12];
	$bobby = $arr[13];
	$sales = $arr[14];
	$url = $arr[17];
	$title = $arr[16];
	$pcopy = $arr[18];
	$mcopy = $arr[19];
	$msg = $arr[15];
	$lmsg = $arr[22];
        $ekiid = $arr[23];
	$status = 0;
	$entry = $arr[20];
	$now = date( "Y-m-d H:i:s" );
	$lid = $arr[21];
	$stmt->execute();
	$stmt->close();
	$cnn->close();

}

// *******************************************
// 駅ちか同期確認用
// *******************************************
function responseEki($shop_autonum,$eki_id,$eki_pass){
	$URL = "https://ranking-deli.jp/api/eki-g1/v1/shops/".$shop_autonum."/admin";

	$token = 'gPw4lc29bcgCpJWZWrFNVkeQ82sVDlybYUZsqbLGJn50R';
	$query_string = '';

	$key = base64_decode($token);
	$digest = hash_hmac('sha256', $query_string, $key, true);
	$signature = base64_encode($digest);

	$account = $eki_id . ':' . $eki_pass;
	$account = openssl_encrypt($account, 'aes-256-ecb', $key);
	$account = base64_encode($account);

	// curlの処理を始める合図
	$curl = curl_init($URL);

	// リクエストのオプションをセットしていく
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-Ekichika-Signature:'.$signature,'X-Ekichika-Authorization:'.$account));
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	// レスポンスを変数に入れる
	$response = curl_exec($curl);

	// curlの処理を終了
	curl_close($curl);
	return $response;
}

// *******************************************
// ロギング用
// *******************************************
function loging($data){
// ログ保存ファイル名
//define("TXTFILE", "../log.txt");
$myFile = "../log.txt";

$fh = fopen($myFile, "a+");

//行数取得
$count = exec('wc -l '.$myFile);
$count = trim(str_replace($myFile, '', $count));

//書き込み
$str = $data;
//$str = mb_convert_encoding($data,"ISO-2022-JP","UTF-8");
fputs($fh,  date("Y-m-d H:i:s") . ' ' . $str . "\n");

//指定行まで削除
$myFile_d = file($myFile);
$count_d = $count;
while($count_d > 500){
unset($myFile_d[0]);
$myFile_d = array_values($myFile_d);
$count_d = $count_d-1;
}

//更新
file_put_contents($myFile, $myFile_d);

fclose($fh);
}
