<?php
	session_start();

	error_reporting(E_ALL);
add_action( 'init', 'amumu_board_init' );
add_action( 'init', 'amumu_sns_init' );
add_action( 'the_content','amumu_board_make' );
add_action( 'the_content','amumu_sns_comment' );
add_action( 'admin_menu', 'amumu_board_setting_page' );
add_action( 'admin_menu', 'amumu_sns_setting_page' );
add_action( 'admin_notices','amumu_board_notice' );

add_filter('upload_mimes', 'amumu_custom_upload_mimes');

function amumu_custom_upload_mimes ( $existing_mimes=array() ) {  
    $existing_mimes['hwp'] = 'application/hangul'; 
    return $existing_mimes; 
}

function amumu_board_setting_page() {
   add_menu_page('Amumu-Board', 'Amumu-Board', 'edit_themes', 'amumu-board/amumu-board-settings.php', '',   '', 1002);
   add_submenu_page('amumu-board/amumu-board-settings.php', 'Amumu Admin', 'Amumu Admin', 'edit_themes', 'amumu-board/amumu-board-admin.php', '');
}

function amumu_sns_setting_page() {
   add_menu_page('Amumu-SNS', 'Amumu-SNS', 'edit_themes', 'amumu-board/amumu-sns-settings.php', '',   '', 1000);
}

error_reporting(0);

function amumu_board_init() {
	$amumu_board_options = array( 'is_allow' => false,
								  'per_page' => 20,
								  'is_category_page' => '',
								  'no_write' => ''
								);

	add_option('amumu_board_options', $amumu_board_options);
	
	$options = get_option( 'amumu_board_options' );
/*
	if ( empty( $options['is_allow'] ) || $options['is_allow'] == false )
		return;
*/
	add_action( 'wp_head', 'amumu_board_head' );
	add_action( 'admin_head', 'amumu_board_head' );
	wp_enqueue_script("jquery");
}

function amumu_sns_init() {
	$amumu_sns_options = array('is_allow' 	=> false,
						'amumu_sns_api_key' => '',
						'facebook_app_id' => '',
						'allow_page' => '',
						);

	// No options yet?
	add_option('amumu_sns_options', $amumu_sns_options);
	
	$options = get_option( 'amumu_sns_options' );

	if ( empty( $options['facebook_app_id'] ) )
		return;
	add_action( 'wp_head', 'amumu_sns_head' );
	add_action( 'admin_head', 'amumu_sns_head' );
	//wp_enqueue_script("jquery");
}

function amumu_board_activate() {
	//WordPress Amumu Board Create Table
	amumu_board_install();
}

function amumu_deactivation(){
	
}

function amumu_board_install() {
   global $wpdb,$amumu_board_ver;

   $table_name = $wpdb->prefix . "amumu_board";
   $table_name2 = $wpdb->prefix . "amumu_board_comments";
	  
   $sql = "CREATE TABLE IF NOT EXISTS ". $table_name." (
			  id bigint(20) unsigned NOT NULL auto_increment,
			  `subject` varchar(255) NOT NULL default '',
			  `author_id` varchar(255) default '0',
			  `parent_id` bigint(20) NOT NULL,
			  `author_email` varchar(255) NOT NULL default '',
			  `author_name` varchar(50) default '',
			  `author_pic` varchar(255) default '',
			  `author_url` varchar(255) default '',
			  `date` datetime NOT NULL default '0000-00-00 00:00:00',
			  `text` text,
			  category int(11),
			  passwd varchar(50) default '',
			  file_name varchar(255) default '',
			  is_secret boolean not null default 0,
			  is_notice boolean not null default 0,
			  views int(11) NOT NULL default '0',
			  PRIMARY KEY (id),
			  FULLTEXT(`text`),
			  FULLTEXT(`subject`)
			) ENGINE=MYISAM DEFAULT CHARACTER SET = utf8;";
	
	$sql2 = "CREATE TABLE IF NOT EXISTS ". $table_name2 ." (
				`id` bigint(20) NOT NULL auto_increment,
				`parent_id` bigint(20) NOT NULL,
				`parent_comment_id` bigint(20) NOT NULL,
				`author_id` varchar(255) default '0',
				`author_email` varchar(255) NOT NULL default '',
				`author_name` varchar(50) NOT NULL default '',
				`author_pic` varchar(255) default '',
				`author_url` varchar(255) default '',
				`date` datetime NOT NULL default '0000-00-00 00:00:00',
				`text` text,
				`like` int(11) NOT NULL default '0',
				PRIMARY KEY (id),
				FULLTEXT(`text`)
			) ENGINE=MYISAM DEFAULT CHARACTER SET = utf8;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	dbDelta($sql2);

	add_option("amumu_board_db_version", $amumu_board_ver);
}

function amumu_board_uninstall(){
	global $wpdb;
	
	$drop_table = "DROP TABLE `".$wpdb->prefix."amumu_board`,`".$wpdb->prefix."amumu_board_comments`";
	$wpdb->query($drop_table);
}

function amumu_board_submit_dialog($message, $post_ID, $error = false, $back = '') {
	if ($error) {
		$class = 'error amumu-board-submit';
	}
	else {
		$class = 'updated amumu-board-submit';
	}

	if($back == "back"){
		echo '<div class="' . $class .'"><p>'. $message . '</p><a style="cursor:pointer" onclick="javascript:history.go(-1);">확인</a></div>';
	}else{
		echo '<div class="' . $class .'"><p>'. $message . '</p><a href="'.get_amumu_board_link('',$post_ID).'">확인</a></div>';
	}
}

function amumu_strlen_utf8($str, $checkmb = false) {
	preg_match_all('/[\xE0-\xFF][\x80-\xFF]{2}|./', $str, $match); // target for BMP

	$m = $match[0];
	$mlen = count($m); // length of matched characters

	if (!$checkmb) return $mlen;
	$count=0;
		for ($i=0; $i < $mlen; $i++) {
		$count += ($checkmb && strlen($m[$i]) > 1)?2:1;
		}

	return $count;

}

function amumu_str_to_limit($str, $num = 30){
	
	$str_len = amumu_strlen_utf8($str);
	if($str_len > $num){
		$str_start = mb_substr($str,0,$num);
		$str_end = "...";
	}else{
		return $str;
	}

	return $str_start.$str_end;
}

function amumu_board_notice(){

	if (function_exists('curl_init')) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, AMUMU_BOARD_UPDATE_URL);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 600);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0');
		$data = curl_exec($ch);
		$data = simplexml_load_string($data);
		
		curl_close($ch);
	} else {
		// curl library is not installed so we better use something else
		$xml = @wp_remote_get(AMUMU_BOARD_UPDATE_URL);
		$data = @simplexml_load_string($xml['body']);
	}

	if(is_object($data)){
	$is_update = false;
	if($data->version != AMUMU_BOARD_VERSION){
		$is_update = true;
	}
	if (! current_user_can('manage_options') || $is_update == false ) 
        return;
	echo "<div class=\"updated\"><p>현재 설치되어 있는 워드프레스 게시판 \"Amumu Board\"의 버전은 ".AMUMU_BOARD_VERSION."이며 버전 {$data->version} 가 새로 배포되었습니다.</p>";
	echo "<p>다운로드 받으러 가기: <a href='{$data->download}'>$data->download</a></p></div>";
	}

}

function amumu_get_list_count($category, $addsql){
	global $wpdb;

	return $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."amumu_board WHERE category = ".$category." AND is_notice = 0 AND parent_id = 0".$addsql);

}

function amumu_get_list($where, $orderby="id", $offset = 0 , $limit = 10, $no){
		global $wpdb;

		$wpdb->show_errors();


		$query = "SELECT * FROM ".$wpdb->prefix."amumu_board WHERE 1=1 {$where} ORDER BY {$orderby}";

		if( $limit > 0 ) $query .= " LIMIT {$offset}, {$limit}";

		$data = $wpdb->get_results( $query );

		return $data;
}

function amumu_get_content_reply_list($post_ID, $parent_id){
		global $wpdb;

		$query = "SELECT * FROM ".$wpdb->prefix."amumu_board WHERE is_notice = 0 AND category = ".$post_ID." AND parent_id = ".$parent_id." ORDER BY id ASC";

		$data = $wpdb->get_results( $query );

		return $data;
}

function amumu_get_notice_list($post_ID){
		global $wpdb;

		$query = "SELECT * FROM ".$wpdb->prefix."amumu_board WHERE is_notice = 1 AND category = ".$post_ID." ORDER BY id DESC";
		$data = $wpdb->get_results( $query );

		return $data;
}

function amumu_base64_url_encode($input)
{
    return strtr(base64_encode($input), '+/=', '-_,');
}
 
function amumu_base64_url_decode($input)
{
    return base64_decode(strtr($input, '-_,', '+/='));
}

function amumu_board_make($content){

	global $post,$wpdb,$user_login,$user_email,$user_ID,$amumu_sns_facebook;

	$amumu_sns_facebook = isset($_SESSION['amumu_sns_facebook']) ? $_SESSION['amumu_sns_facebook'] : $user_ID;

	$amumu_sns_name = isset($_SESSION['amumu_sns_name']) ? $_SESSION['amumu_sns_name'] : $user_login;
	$amumu_sns_pic = isset($_SESSION['amumu_sns_pic']) ? $_SESSION['amumu_sns_pic'] : null;
	$amumu_sns_url = isset($_SESSION['amumu_sns_url']) ? $_SESSION['amumu_sns_url'] : null;
	$options = get_option( 'amumu_board_options' );
	$allow_page = explode(",",$options['is_category_page']);
	$post_ID = $post->ID;
	$is_allow = in_array($post_ID,$allow_page);

	if( $post->post_type =="page" && ( $is_allow && $options['is_allow'] == true ) ) {

		require_once 'amumu-board-paging.php';

		$action = isset($_REQUEST['action']) ? strip_tags($_REQUEST['action']) : null;
		$submit_type = isset($_REQUEST['submit_type']) ? strip_tags($_REQUEST['submit_type']) : null;
		$pid = isset($_REQUEST['pid']) ? strip_tags($_REQUEST['pid']) : null;
		$rid = isset($_REQUEST['rid']) ? strip_tags($_REQUEST['rid']) : null;
		$passwd = isset($_REQUEST['passwd']) ? strip_tags(trim($_POST['passwd'])) : null;

		if($submit_type == "add_post" && $action == "add_post"){

			if(isset($_FILES['upload_file'])) $is_upload = $_FILES['upload_file']['error'];
			else $is_upload = 4;

			$uploadfilename = "";

			if($is_upload != 4 && $is_upload == 0){
				$uploaddir = AMUMU_BOARD_UPLOAD_DIR;
				$add_filename = date('Ymd');

				//$uploadfile = $uploaddir.urlencode($add_filename."_".$_FILES['upload_file']['name']);
				//$uploadfilename = urlencode($add_filename."_".$_FILES['upload_file']['name']);

				$uploadfile = $uploaddir.$add_filename."_".$_FILES['upload_file']['name'];
				$uploadfilename = $add_filename."_".$_FILES['upload_file']['name'];

				if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $uploadfile)) {
					//echo "파일이 유효하고, 성공적으로 업로드 되었습니다.\n";
				}else{
					//print "파일 업로드 공격의 가능성이 있습니다!\n";
					return amumu_board_submit_dialog("이미지 업로드 실패 관리자에게 문의하세요.",$post_ID,true);
				}
			}

			$is_secret = false;
			$is_notice = false;
			$subject = trim($_POST['subject']);
			$author_id = $_POST['post_author_id'] != 0 ? trim($_POST['post_author_id']) : 0;
			$author = trim($_POST['post_author']);
			$author_email = trim($_POST['post_author_email']);
			$author_pic = isset($_POST['post_author_pic']) ? $_POST['post_author_pic'] : '';
			$author_url = isset($_POST['post_author_url']) ? $_POST['post_author_url'] : '';
			$message = trim($_POST['message']);
			$is_secret = isset($_POST['is_secret']) ? $_POST['is_secret'] : '';
			$is_notice = isset($_POST['is_notice']) ? $_POST['is_notice'] : '';
			
			if($is_secret == "on") $is_secret = true;
			if($is_notice == "on") $is_notice = true;

			$table_name = $wpdb->prefix."amumu_board";
			
			$wpdb->show_errors();

			if($author_id != 0){
				$rows_affected = $wpdb->query( $wpdb->prepare(
								"	
									INSERT INTO $table_name
									(parent_id, subject, author_id, author_name, author_email, author_pic, author_url, date, text, category, is_secret, is_notice, file_name, views )
									VALUES ( %d, %s, %s, %s, %s, %s, %s, %s, %s, %d, %d, %d, %s, %d )
								",
								$rid,
								$subject,
								$author_id,
								$author,
								$author_email,
								$author_pic,
								$author_url,
								current_time('mysql'),
								$message,
								$post_ID,
								$is_secret,
								$is_notice,
								$uploadfilename,
								0
								) );
			}else if($author_id == 0) {
				$rows_affected = $wpdb->query( $wpdb->prepare(
								"	
									INSERT INTO $table_name
									(parent_id, subject, author_id, author_name, author_email, author_pic, author_url, date, text, category, is_secret, is_notice, views, passwd )
									VALUES ( %d, %s, %s, %s, %s, %s, %s, %s, %s, %d, %d, %d, %d , %s)
								",
								$rid,
								$subject,
								$author_id,
								$author,
								$author_email,
								$author_pic,
								$author_url,
								current_time('mysql'),
								$message,
								$post_ID,
								$is_secret,
								$is_notice,
								0,
								$passwd
								) );
			}

			if($rows_affected == 1){
				return amumu_board_submit_dialog("저장되었습니다.",$post_ID,true);
			}else{
				return amumu_board_submit_dialog("DataBase 오류 입니다.",$post_ID,true);
			}

			
		}else if($submit_type == "edit_post" && $action == "edit_post"){
			$subject = trim($_POST['subject']);
			$author_id = $_POST['post_author_id'] != 0 ? trim($_POST['post_author_id']) : 0;
			$message = trim($_POST['message']);
			$pid = trim($_POST['pid']);

			$table_name = $wpdb->prefix."amumu_board";
			
			$wpdb->show_errors();
			if($author_id == 1) {
				$rows_affected = $wpdb->query( $wpdb->prepare( 
									"
										UPDATE $table_name
										SET subject = %s, text = %s
										WHERE id = %d
									",
									$subject,
									$message,
									$pid
								) );
			}else{
				$rows_affected = $wpdb->query( $wpdb->prepare( 
					"
						UPDATE $table_name
						SET subject = %s, text = %s
						WHERE id = %d AND author_id = %d
					",
					$subject,
					$message,
					$pid,
					$author_id
				) );
			}

			if($rows_affected == 1){
				return amumu_board_submit_dialog("저장되었습니다.",$post_ID,true);
			}else{
				return amumu_board_submit_dialog("DataBase 오류 입니다.",$post_ID,true);
			}
		}

		if($action == "add_post"){
			$readonly = "";
			$author_email = "";
			if(current_user_can('manage_options')){
				$author_name = $user_login;
				$author_email = $user_email;
				$amumu_sns_facebook = $user_ID;
				$amumu_sns_pic = "";
                $amumu_sns_url = "";
				$readonly = "readonly";
			}else if($amumu_sns_facebook != 0) {
				$author_name = $_SESSION['amumu_sns_name'];
				$author_email = $_SESSION['amumu_sns_email'];
				$readonly = "readonly";
			}else{
				$author_name = "";
			}
			//wp_editor( '바른말을 씁시다.', 'editor', array('media_buttons' => false, 'textarea_name' => 'message', 'textarea_rows' => 15, 'tinymce' => false));
			$content = "<div class='amumu-board-add-post'>";
			$content .= "<form action='' id='add_post' name='add_post' method='post' enctype='multipart/form-data'>";
			$content .= "<table width='100%'>
							<colgroup>
								<col width='100px;' />
								<col width='*' />
							</colgroup>
						<tr>	
							<th>".__("제목", "amumu_board")."</th>
							<td><input type='text' name='subject' id='check_sbj'/></td>
						</tr>
						<tr>	
							<th>".__("작성자", "amumu_board")."</th>
							<td><input type='text' name='post_author' value='$author_name' $readonly id='check_nm'/></td>
						</tr>
						<tr>	
							<th>".__("이메일", "amumu_board")."</th>
							<td><input type='text' name='post_author_email' value='$author_email' $readonly id='check_email'/></td>
						</tr>
						<tr>
							<td colspan='2'><textarea rows='20' cols='35' name='message' id='check_msg'></textarea></td>
						</tr>";
			
			$content .= amumu_get_password();
			$content .= amumu_get_secret();
			$content .= amumu_get_notice();
			$content .= amumu_get_upload_file();

			$content .= "<tr>
				<td colspan='2' style='text-align:right;'><a id='insert_btn' class='amumu-board-submit'>저장</a></td>
				<input type='hidden' name='submit_type' value='add_post' />
				<input type='hidden' id='post_author_id' name='post_author_id' value='".$amumu_sns_facebook."'/>
				<input type='hidden' id='post_author_pic' name='post_author_pic' value='".$amumu_sns_pic."'/>
				<input type='hidden' id='post_author_url' name='post_author_url' value='".$amumu_sns_url."'/>
				<input type='hidden' name='pid' value='".$post->ID."'/>
				<input type='hidden' name='rid' value='".$rid."'/>
			</tr>

			</table></form>";

			$content .= "<div class='amumu-board-foot'>".amumu_board_menu()."</div>";
			$content .= "</div>";
		}else if($action == "edit_post") {

			if( $passwd != null){
					$result = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."amumu_board WHERE id = ".$pid." and passwd='".$passwd."' AND author_id = '".$amumu_sns_facebook."'");

				if(!$result){
					return amumu_board_submit_dialog("일치하는 정보가 없습니다.",$post_ID,true);
				}
			}

			if(current_user_can('manage_options')){
				$post_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."amumu_board WHERE id = ".$pid);
			}else if($amumu_sns_facebook != 0){
				$post_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."amumu_board WHERE id = ".$pid." and author_id= ".$amumu_sns_facebook);
			}else if($amumu_sns_facebook == 0){
				$post_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."amumu_board WHERE id = ".$pid." and passwd='".$passwd."'");
			}
			
			if(sizeof($post_data) == 0){
				return amumu_board_submit_dialog("잘못된 접근입니다.",$post_ID,true);
			}

			$subj = htmlentities($post_data->subject, $quote_style = ENT_QUOTES,'UTF-8');
			$content  = "<div class='amumu-board-add-post'>";
			$content .=		"<form action='' id='edit_post' name='edit_post' method='post'>";
			$content .=		"<table class='amumu-board-table' width='100%'>
								<colgroup>
									<col width='80px;' />
									<col width='*' />
								</colgroup>
								<tr>	
									<td>".__("제목", "amumu_board")."</td>
									<td><input type='text' name='subject' id='check_sbj' value='".stripslashes($subj)."'/></td>
								</tr>
								<tr>	
									<td>".__("작성자", "amumu_board")."</td>
									<td><input type='text' name='post_author' value='".$post_data->author_name."' readonly id='check_nm'/></td>
								</tr>
								<tr>
									<td colspan='2'><textarea rows='20' cols='35' name='message' id='check_text'>".stripslashes($post_data->text)."</textarea></td>
								</tr>
								<tr>
									<td colspan='2' style='text-align:right;'><a id='edit_post_btn' class='amumu-board-submit'>저장</a></td>
									<input type='hidden' name='submit_type' value='edit_post' />
									<input type='hidden' name='post_author_id' value='".$amumu_sns_facebook."'>
									<input type='hidden' name='post_ID' value='".$post->ID."'/>
									<input type='hidden' name='pid' value='".$post_data->id."'/>
								</tr>
							</table>
							</form>
							<div class='amumu-board-foot'>".amumu_board_menu()."</div>
						</div>";
		}else if($action == "del_post") {
			
			if($passwd != null && $amumu_sns_facebook == 0){

				$result = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."amumu_board WHERE id = ".$pid." AND passwd = '".$passwd."' AND author_id = '".$amumu_sns_facebook."'");
				if(!$result){
					return amumu_board_submit_dialog("일치하는 정보가 없습니다.",$post_ID,true);
				}

				$wpdb->show_errors();
				$rows_affected = $wpdb->query("DELETE FROM ".$wpdb->prefix."amumu_board WHERE id = ".$pid." AND passwd = '".$passwd."' AND author_id = '".$amumu_sns_facebook."'");
				if($rows_affected == 1){
					return amumu_board_submit_dialog("삭제 되었습니다.",$post_ID,true);
				}else{
					return amumu_board_submit_dialog("DataBase 오류 입니다.",$post_ID,true);
				}
			}else{
				$wpdb->show_errors();
				if(current_user_can('manage_options')){
					$rows_affected = $wpdb->query("DELETE FROM ".$wpdb->prefix."amumu_board WHERE id = ".$pid);
				}else{
					$rows_affected = $wpdb->query("DELETE FROM ".$wpdb->prefix."amumu_board WHERE id = ".$pid." AND author_id = ".$amumu_sns_facebook);
				}
				if($rows_affected == 1){
					return amumu_board_submit_dialog("삭제 되었습니다.",$post_ID,true);
				}else{
					return amumu_board_submit_dialog("DataBase 오류 입니다.",$post_ID,true);
				}
			}
		}else if($action == "view") {

			//글 상세 보기
			$posts = amumu_get_board_content($pid);

			//비밀글 처리
			if($posts->is_secret){
				$checked_pw = false;

				if($submit_type == "check_pw"){
					$result = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."amumu_board WHERE category=".$post->ID." AND (id=".$pid." AND passwd='".strip_tags($_POST['secret_passwd'])."')");
					if($result == 1){
						$checked_pw = true;
					}else{
						$checked_pw = false;
					}

				}

				if( (current_user_can('manage_options') || ( $amumu_sns_facebook == $posts->author_id && $amumu_sns_facebook != 0 ) || $checked_pw == true) ){
				}else if($amumu_sns_facebook != $posts->author_id || $posts->author_id != 0){
					return amumu_board_submit_dialog("비밀글은 작성자와 관리자만 확인 할 수 있습니다.",$post_ID,true);
				}else{
					$message = "비밀번호를 입력해 주시기 바랍니다.";
					$content = "<div class='amumu-board-password'>". $message . " <form id='check_pw' action='".get_amumu_board_link('view',$post->ID,strip_tags($pid))."' method='post'><input type='text' id ='secret_passwd' name='secret_passwd'/><input type='hidden' name='submit_type' value='check_pw' /><a id='edit_post_btn' class='amumu-board-submit' onclick='jQuery(\"#check_pw\").submit(); return false;' >확인</a></form></div>";
					return $content;
				}
			}

			// 조회수 올라가는 부분
			$wpdb->query("UPDATE ".$wpdb->prefix."amumu_board SET views = views+1 WHERE id = ".$pid);
			$posts = amumu_get_board_content($pid);

			if($posts->author_pic != ""){
				$content .= "<div class='amumu-board'>
								<div class='amumu-board-head'>
									<h1><img src='".$posts->author_pic."' /> <span class='head-author'>".$posts->author_name."</span><a href ='".$posts->author_url."' target='_blank'><img src='".WP_PLUGIN_URL."/amumu-board/image/facebook.gif' /></a><span class='head-date'>".$posts->date."</span> <span class='head-views'>조회수 : ".$posts->views."</span></h1>
									<h2>".stripslashes($posts->subject)."</h2>";
				$content .=		"</div>";
			}else{
				$content .= "<div class='amumu-board'>
								<div class='amumu-board-head'>
									<h1>".get_avatar( $posts->author_email, $size = '48' )."<span class='head-author'>".$posts->author_name."</span><span class='head-date'>".$posts->date."</span> <span class='head-views'>조회수 : ".$posts->views."</span></h1>
									<h2>".stripslashes($posts->subject)."</h2>";
				$content .=		"</div>";
			}

			$content .=		"<div class='amumu-board-body' id='postid-".$posts->id."'>";
			$content .= stripslashes(nl2br($posts->text));
			//$txt = amumu_my_nl2br($txt);
			$content .=		"</div>";

			$attachment = "";
			if($posts->file_name != ''){
				$attachment =			"<p class='amumu-board-attachment'>첨부파일 : <a href='".WP_PLUGIN_URL."/amumu-board/uploads/".$posts->file_name."' target='_blank'>".urldecode($posts->file_name)."</a></p>";
			}
			$content .=		$attachment;
			
			$action_del = get_amumu_board_link('del_post',$post_ID,$pid,true);
			$action_edit = get_amumu_board_link('edit_post',$post_ID,$pid,true);

			$input_passwd_del = "<div id='guest_passwd_del' class='guest_passwd' style='display:none'><form id='passwd_del' action='".$action_del."' method='post'><input type='password' name='passwd'/><a id='edit_post_btn' class='amumu-board-submit-no' onclick='jQuery(\"#passwd_del\").submit(); return false;' >확인</a></form></div>";

			$input_passwd_edit = "<div id='guest_passwd_edit' class='guest_passwd' style='display:none'><form id='passwd_edit' action='".$action_edit."' method='post'><input type='password' name='passwd'/><a id='edit_post_btn' class='amumu-board-submit-no' onclick='jQuery(\"#passwd_edit\").submit(); return false;' >확인</a></form></div>";

			$content .= "<div class='amumu-board-foot'>".$input_passwd_del.$input_passwd_edit.amumu_board_menu_in($pid,$posts->author_id,$posts->parent_id).amumu_board_menu()."</div>";

			$no_table = true;
			$reply_list = amumu_get_reply_content($pid);

			if(sizeof($reply_list) == 0) $no_table = false;

			
			$content .="<div id='amumu-board-reply'>";
			if($no_table){
				for($i = 0; $i < sizeof($reply_list); $i++){
					$content .= "<div id='amumu-board-reply-".$reply_list[$i]->id."'>";
					$content .= "<div class='amumu-board-reply-wrap-one'>";

					if($reply_list[$i]->author_pic != ""){
						$content .=		"<span class='reply-author'><img src ='".$reply_list[$i]->author_pic."' /> ".$reply_list[$i]->author_name."</span>";
					}else{
						$content .=		"<span class='reply-author'>".get_avatar( $reply_list[$i]->author_email, $size = '48' )." ".$reply_list[$i]->author_name."</span>";
					}


					$content .=		"<span class='reply-date'>".$reply_list[$i]->date."</span>";
					if($amumu_sns_facebook != null){
						$content .=		" <img src='".WP_PLUGIN_URL."/amumu-board/image/re.png' style='box-shadow:none;'><span class='ins-reply-reply' rel='".$reply_list[$i]->id."'>답글</span>";
					}
					$content .=		amumu_board_menu_reply($pid,$reply_list[$i]->author_id,$reply_list[$i]->id);
					$content .=		"<span class='reply_tool' style='display:none;'><span class='mod_reply' value='".$reply_list[$i]->id."'>확인</span><span class='esc_reply' value='".$reply_list[$i]->id."'>취소</span></span>";
					$content .=	"</div>";
					$content .= "<div class='amumu-board-reply-wrap-two'>";
					$content .= "<p class='amumu-baord-reply-p' id='amumu-board-reply-text-p-".$reply_list[$i]->id."'>".nl2br(stripslashes($reply_list[$i]->text))."</p>";
					$content .=		"<textarea id='amumu-board-reply-text-".$reply_list[$i]->id."' class='amumu-board-reply-text' disabled='disabled'>".nl2br(stripslashes($reply_list[$i]->text))."</textarea>";
					$content .=		"<input type='hidden' name='reply_id' value='".$reply_list[$i]->id."' />";
					$content .= "</div>";
					$content .= "</div>";

					$reply_reply_list = amumu_get_reply_reply_content($pid,$reply_list[$i]->id);

					if(sizeof($reply_reply_list) != 0){
						for($j = 0; $j < sizeof($reply_reply_list); $j++){
							$content .= "<div id='amumu-board-reply-".$reply_reply_list[$j]->id."' class='amumu-board-indent1  amumu-board-reply-reply-".$reply_reply_list[$j]->parent_comment_id."'>";
							$content .= "<div class='amumu-board-reply-wrap-one'>";

							if($reply_reply_list[$j]->author_pic != ""){
								$content .=		"<span class='reply-author'><img src ='".$reply_reply_list[$j]->author_pic."' /> ".$reply_reply_list[$j]->author_name."</span>";
							}else{
								$content .=		"<span class='reply-author'>".get_avatar( $reply_reply_list[$j]->author_email, $size = '48' )." ".$reply_reply_list[$j]->author_name."</span>";
							}

							$content .=		"<span class='reply-date'>".$reply_reply_list[$j]->date."</span>";
							$content .=		amumu_board_menu_reply($pid,$reply_reply_list[$j]->author_id,$reply_reply_list[$j]->id);
							$content .=		"<span class='reply_tool' style='display:none;'><span class='mod_reply' value='".$reply_reply_list[$j]->id."'>확인</span><span class='esc_reply' value='".$reply_reply_list[$j]->id."'>취소</span></span>";
							$content .=	"</div>";
							$content .= "<div class='amumu-board-reply-wrap-two'>";
							$content .= "<p class='amumu-baord-reply-p' id='amumu-board-reply-text-p-".$reply_reply_list[$j]->id."'>".nl2br(stripslashes($reply_reply_list[$j]->text))."</p>";
							$content .=		"<textarea id='amumu-board-reply-text-".$reply_reply_list[$j]->id."' class='amumu-board-reply-text' disabled='disabled'>".nl2br(stripslashes($reply_reply_list[$j]->text))."</textarea>";
							$content .=		"<input type='hidden' name='reply_id' value='".$reply_reply_list[$j]->id."' />";
							$content .= "</div>";
							$content .= "</div>";
						}
					}
				}
			};
			$content .="</div>";

			if($amumu_sns_facebook != 0 || current_user_can('manage_options')){

				$amumu_sns_email = "";
				if(current_user_can('manage_options')){
					$amumu_sns_facebook = $user_ID;
					$amumu_sns_name = $user_login;
					$amumu_sns_pic = "";
					$amumu_sns_email = $user_email;
					$amumu_sns_email_hash = md5( $user_email );
				}

				$content .= "<div class='amumu-board-reply-ins'>
								<textarea rows='4' cols='35' id='reply_massage' ></textarea>
								<a id='ins-reply'>댓글입력</a>
								<input type='hidden' id='reply_author_id' value='".$amumu_sns_facebook."'/>
								<input type='hidden' id='reply_author_name' value='".$amumu_sns_name."' />
								<input type='hidden' id='reply_author_pic' value='".$amumu_sns_pic."' />
								<input type='hidden' id='reply_author_email' value='".$amumu_sns_email."' />
								<input type='hidden' id='reply_author_email_hash' value='".$amumu_sns_email_hash."' />
								<input type='hidden' id='parent_ID' value='".$pid."' />";
				$content .= "</div>";
				$content .= "<div class='amumu-board-reply-reply-ins'>
								<textarea rows='4' cols='35' id='reply_reply_massage' ></textarea>
								<a id='ins-reply-reply'>답글입력</a>
								<a id='cancel-reply-reply'>취소</a>
								<input type='hidden' id='reply_reply_author_id' value='".$amumu_sns_facebook."'/>
								<input type='hidden' id='reply_reply_author_name' value='".$amumu_sns_name."' />
								<input type='hidden' id='reply_reply_author_pic' value='".$amumu_sns_pic."' />
								<input type='hidden' id='reply_reply_author_email' value='".$amumu_sns_email."' />
								<input type='hidden' id='reply_reply_author_email_hash' value='".$amumu_sns_email_hash."' />
								<input type='hidden' id='reply_parent_ID' value='".$pid."' />
								<input type='hidden' id='parent_comment_ID' value='' />";
				$content .= "</div>";
			}else{
				$content .= "<div class='amumu-board-reply-ins'>
								<p class='need_login'>댓글 작성은 로그인 후에 이용 가능 합니다.</p>";
				$content .= "</div>";
			}

			$content .= "</div>";

		}else{

			// 시간순정렬관련
			$orderby = isset( $_REQUEST['orderby'] ) ? strip_tags($_REQUEST['orderby']) : 'DESC';
			$keyword = isset( $_REQUEST['keyword'] ) ? strip_tags($_REQUEST['keyword']) : '';
			
			$addsql = "";
			$addsql = $keyword == '' ? "" : " AND ( `subject` like '%$keyword%' OR `text` like '%$keyword%' )";

			// 카테고리 명
			$category = $post->ID;

			// paging
			$this_page   = isset( $_REQUEST['this_page'] ) ? strip_tags($_REQUEST['this_page']) : 1;
			$page_id   = isset( $_REQUEST['page_id'] ) ? strip_tags($_REQUEST['page_id']) : '';
			$total_count = amumu_get_list_count($category,$addsql);
			$num_per_page = $options['per_page'];

			$paging      = new amumu_paging( $total_count, $this_page, array( 'page_id' => $page_id, 'orderby' => $orderby, 'keyword' => $keyword ), $num_per_page, 5 );
			$no          = $paging->no;


			// 출력데이터
			$data = amumu_get_list("AND category = ".$post->ID." AND is_notice =0 AND parent_id = 0".$addsql, "id ".$orderby, $paging->offset, $paging->size, $no);

			// 알림글 리스트업 고정 시작
			$notice_list = amumu_get_notice_list($post_ID);

			$allow_page = explode(",",$options['is_category_page']);

			$post_ID = $post->ID;
			$is_allow = in_array($post_ID,$allow_page);

			$amumu_board_plugin_url = WP_PLUGIN_URL."/amumu-board/";
			$num_post = sizeof($data);
			$num_notice = sizeof($notice_list);

			if( $num_post != 0 || $num_notice != 0 ){
				$content .= amumu_board_menu_login();
				$content .= "<div class='amumu-board-list'><table width='100%'>";
				$content .= "<colgroup>";
				$content .= "<col width='9%;'>";
				$content .= "<col width='*'>";
				$content .= "<col width='20%;'>";
				$content .= "<col width='20%;'>";
				$content .= "<col width='10%;'>";
				$content .= "</colgroup>";
				$content .= "<tr><th>No</th><th class='amumu-board-title-th'>제목</th><th class='amumu-board-mobile'>작성자</th><th>날짜</th><th class='amumu-board-mobile'>조회수</th></tr>";

				if(sizeof($notice_list) != 0 ){
					for ($i=0; $i <sizeof($notice_list); $i++) {
						$date = explode(" ",$notice_list[$i]->date);
						if($notice_list[$i]->is_secret) $is_secret = true;

						$content .= "<tr class='amumu-board-notice'>
										<td>★</td>";
						$content .= "	<td class='amumu-board-title'><a href='".get_amumu_board_link('view',$post_ID,$notice_list[$i]->id)."'><strong>".amumu_str_to_limit(stripslashes($notice_list[$i]->subject),25)."</strong></a>".amumu_get_reply_count($notice_list[$i]->id)."</td>";
						$content .= "	<td class='amumu-board-mobile'>".$notice_list[$i]->author_name."</td>
										<td class='amumu-board-date'>".$date[0]."</td>
										<td class='amumu-board-mobile'>".$notice_list[$i]->views."</td>
									</tr>";
					}
				}
				// 알림글 리스트업 고정 끝

				for($i = 0; $i < $num_post ; $i++){
					$date = explode(" ",$data[$i]->date);
					$is_secret = false;
					if($data[$i]->is_secret) $is_secret = true;
					$attachment = "";
					if($data[$i]->file_name != '') $attachment = " <img src ='".WP_PLUGIN_URL."/amumu-board/image/file.gif' class='amumu-board-attachment'/>";
					$author_name = amumu_str_to_limit($data[$i]->author_name,15);

					$content .= "<tr>
									<td>".$no."</td>";
					if($is_secret){
						$content .= "	<td class='amumu-board-title'>비밀글 : <a href='".get_amumu_board_link('view',$post_ID,$data[$i]->id)."'><strong>".amumu_str_to_limit(stripslashes($data[$i]->subject),25)."</strong></a>".amumu_get_reply_count($data[$i]->id).$attachment."<span class='amumu-board-mobile-author'>- ".$author_name."</span></td>";
					}else{
						$content .= "	<td class='amumu-board-title'><a href='".get_amumu_board_link('view',$post_ID,$data[$i]->id)."'><strong>".amumu_str_to_limit(stripslashes($data[$i]->subject),25)."</strong></a>".amumu_get_reply_count($data[$i]->id).$attachment."<span class='amumu-board-mobile-author'>- ".$author_name."</span></td>";
					}
					$content .= "	<td class='amumu-board-mobile'>".$author_name."</td>
									<td class='amumu-board-date'>".$date[0]."</td>
									<td class='amumu-board-mobile'>".$data[$i]->views."</td>
								</tr>";
					
					$content_reply_list = amumu_get_content_reply_list($post_ID,$data[$i]->id);

					if(sizeof($content_reply_list) != 0){
						for($j = 0; $j < sizeof($content_reply_list); $j++){
							$reply_author_name = amumu_str_to_limit($content_reply_list[$j]->author_name,15);
							$date = explode(" ",$content_reply_list[$j]->date);
							$content .= "<tr>
											<td></td>";
							if($is_secret){
								$content .= "<td class='amumu-board-title'><img src='".$amumu_board_plugin_url."image/icoIndent.gif' class='amumu-board-ico' /> 비밀글 : <a href='".get_amumu_board_link('view',$post_ID,$content_reply_list[$j]->id)."'><strong>".amumu_str_to_limit($content_reply_list[$j]->subject,20)."</strong></a>".amumu_get_reply_count($content_reply_list[$j]->id)."<span class='amumu-board-mobile-author'>- ".$reply_author_name."</span></td>";
							}else{
								$content .= "	<td class='amumu-board-title'><img src='".$amumu_board_plugin_url."image/icoIndent.gif' class='amumu-board-ico' /> <a href='".get_amumu_board_link('view',$post_ID,$content_reply_list[$j]->id)."'><strong>".amumu_str_to_limit($content_reply_list[$j]->subject,20)."</strong></a>".amumu_get_reply_count($content_reply_list[$j]->id)."<span class='amumu-board-mobile-author'>- ".$reply_author_name."</span></td>";
							}
							$content .= "	<td class='amumu-board-mobile'>".$reply_author_name."</td>
											<td class='amumu-board-date'>".$date[0]."</td>
											<td class='amumu-board-mobile'>".$content_reply_list[$j]->views."</td>
										</tr>";
						}
					}

					$no--;
				}
				$content .= "<input type='hidden' id='amumu_permal_link' value='".get_amumu_permal_link()."' />";
				$content .= "</table>";
				$content .= "<div class='amumu-board-list-paging'>".$paging->amumu_get_paging()."</div>";
				$content .= "<div class='amumu-board-list-foot'>".amumu_board_search()."</div>";
				$content .= "</div>";
			}else{

				// 작성된 글이 없을 경우
				$content .= amumu_board_menu_login();
				$content .= "<div class='amumu-board-list'><table width='100%'>";
				$content .= "<colgroup>";
				$content .= "<col width='7%;'>";
				$content .= "<col width='*'>";
				$content .= "<col width='15%;'>";
				$content .= "<col width='20%;'>";
				$content .= "<col width='10%;'>";
				$content .= "</colgroup>";
				$content .= "<tr><th>No</th><th class='amumu-board-mobile-title-th'>제목</th><th class='amumu-board-mobile'>작성자</th><th>날짜</th><th class='amumu-board-mobile'>조회수</th></tr>";
				$content .= "<tr>
								<td colspan=5>작성된 글이 없습니다.</td>
							</tr>";
				$content .= "<input type='hidden' id='amumu_permal_link' value='".get_amumu_permal_link()."' />";
				$content .= "</table>";
				$content .= "<div class='amumu-board-list-paging'>".$paging->amumu_get_paging()."</div>";
				$content .= "<div class='amumu-board-list-foot'>".amumu_board_search()."</div>";
				$content .= "</div>";
			}

		}

	}

	return $content;
}

function amumu_sns_comment($content) {
	global $post, $amumu_sns_facebook;

	$options = get_option( 'amumu_sns_options' );
	//$amumu_sns_facebook = isset($_SESSION['amumu_sns_facebook']) ? $_SESSION['amumu_sns_facebook'] : null;

	$allow_page = explode(",",$options['allow_page']);
	$is_allow = in_array($post->ID,$allow_page);

  if( is_single() == 1 && ( $post->post_type !="page" && is_home() == 0) || $is_allow ) {
    if ($options['is_allow']) {
		
		$amumu_sns_thumbnail = amumu_getImg($content);
		//$amumu_sns_comment_excerpt = amumu_sns_excerpt_max_charlength(140);
		$amumu_sns_post_id = $post->ID;
		$amumu_sns_plugin_url = WP_PLUGIN_URL.'/amumu-board/';
            
		$amumu_sns_comment = '<div id="snswrap">
							<div class="snswrite">
								<div class="snschoice">
									<span><button id="amumu-sns-facebook-login" class="off" ></button></span>
									<span class="amumu-sns-logout" ><button id="amumu-sns-logout" class="on"></button></span>
								</div>
								<p><span id="amumu-sns-title">소셜계정으로 로그인 후 작성하세요</span></p>
								<fieldset>
									<span id="amumu-sns-count" class="letter">140 / 140</span>
									<span class="someimg"><button id="amumu-sns-pic"></button></span>
									<textarea id="amumu-sns-text">로그인 후 작성 가능합니다.</textarea>
									<button id="amumu-sns-insert"></button>
								</fieldset>
								<div class="snsp">
									powered by <strong>AMUMU<span>SNS</span></strong>
								</div>
								<!--Facebook-->
								<input type="hidden" id="amumu-sns-facebook-name" />
								<input type="hidden" id="amumu-sns-facebook-authorId" />
								<input type="hidden" id="amumu-sns-facebook-profile-link" />
								<input type="hidden" id="amumu-sns-facebook-pic" />

								<!--Use SNS Service-->
								<input type="hidden" id="amumu-sns-selected" />
								<input type="hidden" id="amumu-sns-offset" value="0"/>
								<input type="hidden" id="amumu-sns-comment-author" />
								<input type="hidden" id="amumu-sns-excerpt" value="'.amumu_sns_excerpt_max_charlength(140).'"/>
								<input type="hidden" id="amumu-sns-page-ID" value="'.$amumu_sns_post_id.'"/>

							</div>
							<div class="snsview">
								<h3>전체댓글수<strong>'.$post->comment_count.'</strong></h3>
								<div class="snsappend"></div>';
		$amumu_sns_comment .= $amumu_sns_last_comment = amumu_sns_content($amumu_sns_post_id);
		$amumu_sns_comment .= '		</div>
							<div class="btnm" id="amumu-sns-more">
								<img src="'.$amumu_sns_plugin_url.'image/btn_newmore.png" alt="MORE" />
							</div>
						</div>

						<div class="loading-indicator" style="display: none; ">
							<img width="32" height="32" src="'.$amumu_sns_plugin_url.'image/loading.gif" alt="">        
							<p>Loading...</p>
						</div>';

		if($amumu_sns_thumbnail != ''){
			echo "<div style='display:none;'><img id='amumu_sns_thumb' src='".$amumu_sns_thumbnail."'/></div>";
		}else{
			echo "<div style='display:none;'><img id='amumu_sns_thumb' src='".$amumu_sns_plugin_url."image/image.jpg'/></div>";
		}
		
		$content .= $amumu_sns_comment;
    }
  }
	return $content;
}

function amumu_board_menu_login(){
	$menu = "";
	$menu .= "<div id='snswrap_main'>
				<div class='snswrite'>
								<div class='snschoice'>
									<span><button id='amumu-sns-facebook-login' class='off' ></button></span>
									<span class='amumu-sns-logout' ><button id='amumu-sns-logout' class='on'></button></span>
								</div>
								<p><span id='amumu-sns-title'></span></p>
				</div>
			</div>";
	return $menu;
}
function amumu_board_menu_reply($pid= '', $author_id = '', $reply_id){
	global $amumu_sns_facebook;

	$menu = "";
		if (($amumu_sns_facebook != 0 && $amumu_sns_facebook == $author_id) || current_user_can('manage_options')) {
			$menu .= "<span class='reply_menu'>";
			$menu .= "<span class='amumu-board-reply-edit' value='".$reply_id."'>" .__("수정", "amumu_board")."</span>";
			$menu .= "<span class='amumu-board-reply-del' value='".$reply_id."'>".__("삭제", "amumu_board")."</span>";
			$menu .= "</span>";
		}

	return $menu;
}

function amumu_board_insert_comment(){
	
	global $wpdb;

	$wpdb->show_errors();
	
	$table_name = $wpdb->prefix."amumu_board_comments";

	$content = $_POST['text'];
	$author_id =  $_POST['author_id'];
	$author_name =  $_POST['author_name'];
	$author_pic =  $_POST['author_pic'];
	$author_email =  $_POST['author_email'];
	$parent_id =  $_POST['parent_id'];
	$parent_comment_id = $_POST['parent_comment_id'];

	$rows_affected = $wpdb->query( $wpdb->prepare(
								"	
									INSERT INTO $table_name
									( parent_id, parent_comment_id, author_id, author_name, author_pic, author_email, date, text, `like` )
									VALUES ( %d, %d, %s, %s, %s, %s, %s, %s, %d )
								",
								$parent_id,
								$parent_comment_id,
								$author_id,
								$author_name,
								$author_pic,
								$author_email,
								current_time('mysql'),
								$content,
								0
								) );
	
	$reply_data = $wpdb->get_row("SELECT id, date FROM ".$table_name." ORDER BY id DESC LIMIT 1");

	$reply_id = $reply_data->id;
	$date = $reply_data->date;

	if($rows_affected){
		echo json_encode(array("result"=> 1, "reply_id" => $reply_id, "parent_comment_id" => $parent_comment_id, "date" => $date));
		die();
	}else{
		echo json_encode(array("result"=> 0));
		die();
	}
}
add_action('wp_ajax_nopriv_amumu_board_insert_comment','amumu_board_insert_comment');
add_action('wp_ajax_amumu_board_insert_comment','amumu_board_insert_comment');

function amumu_board_delete_comment(){
	
	global $wpdb;
	$table_name = $wpdb->prefix."amumu_board_comments";

	$comment_ID = strip_tags($_POST['comment_ID']);

	$reply_author_id = $wpdb->get_var("SELECT author_id FROM ".$table_name." WHERE id = ".$comment_ID);

	if($_SESSION['amumu_sns_facebook'] == $reply_author_id || current_user_can('manage_options')){
		$result = $wpdb->query("DELETE FROM ".$table_name." WHERE id =".$comment_ID);
		$wpdb->query("DELETE FROM ".$table_name." WHERE parent_comment_id =".$comment_ID);
	}else{
		$result = FALSE;
	}
	if($result){
		echo json_encode(array("result"=> 1));
		die();
	}else{
		echo json_encode(array("result"=> 0));
		die();
	}
}
add_action('wp_ajax_nopriv_amumu_board_delete_comment','amumu_board_delete_comment');
add_action('wp_ajax_amumu_board_delete_comment','amumu_board_delete_comment');

function amumu_board_update_comment(){
	
	global $wpdb;

	$table_name = "wp_amumu_board_comments";
	$wpdb->show_errors();

	$content = $_POST['text'];
	$reply_id = $_POST['reply_id'];

	$rows_affected = $wpdb->query( $wpdb->prepare( 
						"
							UPDATE $table_name
							SET text = %s, date = %s
							WHERE id = %d
						",
						$content,
						current_time('mysql'),
						$reply_id
					) );
	
	if($rows_affected){
		echo json_encode(array("result"=> 1));
		die();
	}else{
		echo json_encode(array("result"=> 0));
		die();
	}
}
add_action('wp_ajax_nopriv_amumu_board_update_comment','amumu_board_update_comment');
add_action('wp_ajax_amumu_board_update_comment','amumu_board_update_comment');

function amumu_get_board_content($pid){
	global $wpdb;

	if($pid){
		$posts = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."amumu_board WHERE id = ".$pid);
		return $posts;
	}else{
		return false;
	}
}

function amumu_get_reply_content($pid = ''){
	global $wpdb;

	return $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."amumu_board_comments WHERE parent_id = ".$pid." AND parent_comment_id = 0 ORDER BY `date` ASC");
}

function amumu_get_reply_reply_content($pid = '', $parent_comment_id){
	global $wpdb;

	return $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."amumu_board_comments WHERE parent_id = ".$pid." AND parent_comment_id = ".$parent_comment_id." ORDER BY `date` ASC");
}

function amumu_my_nl2br($string){
	$string = str_replace("\n", "<br />", $string);
	if(preg_match_all('/\<pre\>(.*?)\<\/pre\>/', $string, $match)){
		foreach($match as $a){
			foreach($a as $b){
			$string = str_replace('<pre>'.$b.'</pre>', "<pre>".str_replace("<br />", "", $b)."</pre>", $string);
			}
		}
	}
	return $string;
}

function amumu_get_password(){
	global $user_ID, $amumu_sns_facebook;
	$out = "";

	if($amumu_sns_facebook == 0){
		$out = "<tr>
					<th>비밀번호</th>
					<td><input name='passwd' type='password' id='check_pw'/></td>
				</tr>
				";
	}
	return $out;
}

function amumu_get_secret(){
	$out = "";
	if(!current_user_can('manage_options')){
	$out = "<tr>
				<th>비밀글</th>
				<td><input name='is_secret' type='checkbox'/></td>
			</tr>
			";
	}
	return $out;
}


function amumu_get_notice(){
	$out = "";
	if(current_user_can('manage_options')){
		$out = "<tr>
					<th>알림글</th>
					<td><input name='is_notice' type='checkbox'/></td>
				</tr>
				";
	}
	return $out;
}

function amumu_get_upload_file(){
	global $amumu_sns_facebook;
	$out = "";

	if($amumu_sns_facebook != 0 || current_user_can('manage_options')){
		$out = "<tr>
					<th>첨부파일</th>
					<td><input name='upload_file' type='file'/></td>
				</tr>
				";
	}
	return $out;
}

function amumu_board_menu(){
	global $post;

	$options = get_option('amumu_board_options');
	$no_write = explode(",",$options['no_write']);

	if(!in_array($post->ID, $no_write) || current_user_can('manage_options')){
		$menu = "<a class='amumu-board-menu-list' href='".get_amumu_board_link('post_list',$post->ID)."'>".__("목록", "amumu_board")."</a>";
		$menu .= "<a class='amumu-board-menu-write' href='".get_amumu_board_link('add_post',$post->ID)."'>".__("글쓰기", "amumu_board")."</a>";
	}else{
		$menu = "<a class='amumu-board-menu-write' href='".get_amumu_board_link('post_list',$post->ID)."'>".__("목록", "amumu_board")."</a>";
	}
	return $menu;
}

function amumu_board_search(){
	global $post;

	$menu ="<fieldset>";
	$menu .= "<input type='text' id='amumu_board_search'/>";
	$menu .= "<a class='amumu-board-menu-search' href='".get_amumu_board_link('',$post->ID)."' id='amumu_board_search_submit'>".__("검색", "amumu_board")."</a>";
	$menu .=amumu_board_menu();
	$menu .="</fieldset>";

	return $menu;
}

function amumu_board_menu_in($pid= '', $author_id = '', $parent_id = 0){
	global $post,$amumu_sns_facebook;
		$menu = "";
		if (($amumu_sns_facebook != 0 && $amumu_sns_facebook == $author_id) || (current_user_can('manage_options') && $pid != '')) {
			if(current_user_can('manage_options') && $pid != '' && $parent_id == 0) {
			$menu .= "<a class='amumu-board-menu-add' href='".get_amumu_board_link('add_post',$post->ID,$pid,false,true)."'>답글</a>";
			}
			$menu .= "<a class='amumu-board-menu-edit' href='".get_amumu_board_link('edit_post',$post->ID,$pid)."'>수정</a>";
			$menu .= "<a class='amumu-board-menu-del'onclick=\"return wpf_confirm();\" href='".get_amumu_board_link('del_post',$post->ID,$pid)."'>삭제</a>";
		}else if($amumu_sns_facebook == 0 && $author_id == 0){
			$menu .= "<a id='edit_post' class='amumu-board-menu-edit'>수정</a>";
			$menu .= "<a id='del_post' class='amumu-board-menu-del'>삭제</a>";
		}

	return $menu;
}

function get_amumu_board_link($action, $post_ID = 0, $pid = 0, $is_member = false, $is_reply = false){
	$options = get_option('permalink_structure');

	if($options['option_value'] == ''){
		$permalink_option = "&";
	}else{
		$permalink_option = "?";
	}
	switch ($action) {
		case "view":
			return get_permalink($post_ID).$permalink_option."action=view&pid=".$pid;
			break;
		case "post_list":
			return get_permalink($post_ID);
			break;
		case "add_post":
			if($is_reply){
				return get_permalink($post_ID).$permalink_option."action=add_post&rid=".$pid;
			}else{
				return get_permalink($post_ID).$permalink_option."action=add_post";
			}
			break;
		case "edit_post":
			if($is_member){
				return get_permalink($post_ID).$permalink_option."action=edit_post&pid=".$pid."&passwd=passwd";
			}else{
				return get_permalink($post_ID).$permalink_option."action=edit_post&pid=".$pid;
			}
		case "del_post":
			if($is_member){
				return get_permalink($post_ID).$permalink_option."action=del_post&pid=".$pid."&passwd=passwd";
			}else{
				return get_permalink($post_ID).$permalink_option."action=del_post&pid=".$pid;
			}
			break;
		case "del_reply":
			return get_permalink($post_ID).$permalink_option."action=del_reply&pid=".$pid;
			break;
	
		default :
			return get_permalink($post_ID);
	}
}

function get_amumu_permal_link(){
	$options = get_option('permalink_structure');

	if($options['option_value'] == ''){
		$permalink_option = "&";
	}else{
		$permalink_option = "?";
	}

	return $permalink_option;
}

function amumu_get_reply_count($id){
	global $wpdb;
	$count = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."amumu_board_comments WHERE parent_id = ".(int)$id);
	if(!$count) {
		$count = "";
	}else{
		$count = " [".$count."]";
	}
	return $count;
}

function amumu_board_list($post_ID){
	global $wpdb;
	$options = get_option('amumu_board_options');
	$board_list = explode(",",$options['is_category_page']);
	
	$board_nav = "<div>";
	for ($i=0; $i <sizeof($board_list); $i++) {
		$board_title = $wpdb->get_var("SELECT post_title FROM ".$wpdb->prefix."posts WHERE id =".$board_list[$i]);
		$board_count = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."amumu_board WHERE category =".$board_list[$i]);
		if($post_ID == $board_list[$i]){
			$board_nav .= "<a><font size=3><strong> ".$board_title."[".$board_count."]</strong></font></a> ,";
		}else{
			$board_nav .= "<a href='".get_permalink($board_list[$i])."'><font size=3> ".$board_title."[".$board_count."]</font></a> |";
		}
	}

	$board_nav = substr($board_nav,0,strlen($board_nav) -2);

	$board_nav .= "</div>";

	return $board_nav;
}

function amumu_board_page_list(){
	global $wpdb;
	$page_list = $wpdb->get_results("SELECT post_title,ID FROM ".$wpdb->prefix."posts WHERE post_type='page'");
	$output = "<select name='amumu_board_add'>\n";
	$output .= "\t<option value=\"\">SELECT PAGE</option>\n";
	for ($i=0; $i <sizeof($page_list); $i++) {
		$output .= "\t<option value=\"" . $page_list[$i]->ID . "\">".$page_list[$i]->post_title."</option>\n";
	}
	$output .= "</select>\n";

	echo $output;
}

function amumu_board_nowrite_list(){
	global $wpdb;
	$option = get_option('amumu_board_options');

	$page_list = $wpdb->get_results("SELECT post_title,ID FROM ".$wpdb->prefix."posts WHERE post_type='page' AND ID in (".$option['is_category_page'].")");
	$output = "<select name='amumu_board_add_nowrite'>\n";
	$output .= "\t<option value=\"\">SELECT PAGE</option>\n";
	for ($i=0; $i <sizeof($page_list); $i++) {
		$output .= "\t<option value=\"" . $page_list[$i]->ID . "\">".$page_list[$i]->post_title."</option>\n";
	}
	$output .= "</select>\n";

	echo $output;
}

function amumu_board_list_admin(){
	global $wpdb;
	$option = get_option('amumu_board_options');
	$where_in = $option['is_category_page'];
	$page_list = $wpdb->get_results("SELECT post_title,ID FROM ".$wpdb->prefix."posts WHERE post_type='page' AND ID in (".$where_in.")");
	$output = "<select name='amumu_board_reset_board'>\n";
	$output .= "\t<option value=\"\">SELECT PAGE</option>\n";
	for ($i=0; $i <sizeof($page_list); $i++) {
		$output .= "\t<option value=\"" . $page_list[$i]->ID . "\">".$page_list[$i]->post_title."</option>\n";
	}
	$output .= "</select>\n";

	echo $output;
}

function amumu_sns_insert_comment(){
	
	global $wpdb;

	$insert_date = time();
	$return_date = date('Y-m-d H:i:s',$insert_date);
	$table_name = "wp_comments";
	$table_name_meta = "wp_commentmeta";

	$comment_post_ID = strip_tags($_POST['page_ID']);
	$comment_author = strip_tags($_POST['name']);
	$comment_author_email = "";
	$comment_author_url = strip_tags($_POST['profile_link']);
	$comment_author_IP = "";
	$comment_date = $return_date;
	$comment_date_gmt = $return_date;
	$comment_content = strip_tags($_POST['comment']);
	$comment_karma = "";
	$comment_approved = 1;
	$comment_agent = strip_tags($_POST['comment_author']);
	$comment_type = strip_tags($_POST['comment_type']);
	$comment_parent = 0;
	$user_id = 0;

	$wpdb->show_errors();
	$rows_affected = $wpdb->insert( $table_name, array( 'comment_post_ID' => $comment_post_ID , 'comment_author' => $comment_author, 'comment_author_email' => $comment_author_email, 'comment_author_url' => $comment_author_url, 'comment_date' => $comment_date, 'comment_date_gmt' => $comment_date_gmt, 'comment_content' => $comment_content, 'comment_karma' => $comment_karma , 'comment_approved' => $comment_approved , 'comment_agent' => $comment_agent , 'comment_type' => $comment_type , 'comment_parent' => $comment_parent , 'user_id' => $user_id ) );

	if($wpdb->insert_id){
		wp_update_comment_count( $comment_post_ID );
		$table_name = "wp_commentmeta";
		$meta_key = "amumu-sns-pic";
		$meta_value = strip_tags($_POST['pic']);
		$comment_id = $wpdb->insert_id;
		$rows_affected_meta = $wpdb->insert( $table_name_meta, array( 'meta_key' => $meta_key , 'meta_value' => $meta_value, 'comment_id' => $comment_id ) );
	}
	

	echo json_encode(array("result"=> 1, "plugins_url"=>WP_PLUGIN_URL."/amumu-board/", "date"=>$return_date, "comment_id"=>$comment_id));
	die();
}
add_action('wp_ajax_nopriv_amumu_sns_insert_comment','amumu_sns_insert_comment');
add_action('wp_ajax_amumu_sns_insert_comment','amumu_sns_insert_comment');


function amumu_sns_delete_comment(){
	
	global $wpdb, $amumu_sns_facebook;
	//$amumu_sns_facebook = isset($_SESSION['amumu_sns_facebook']) ? $_SESSION['amumu_sns_facebook'] : null;
	$table_name = "wp_comments";
	$table_name_meta = "wp_commentmeta";

	$comment_ID = $_POST['comment_ID'];
	$comment_id = $_POST['comment_ID'];
	
	if($amumu_sns_facebook != 0 || current_user_can('manage_options')){
		if(wp_delete_comment($comment_ID)){
			$result = delete_comment_meta($comment_id,'amumu-sns-pic');
		}
	}else{
		$result = FALSE;
	}
	if($result){
		echo json_encode(array("result"=> 1));
		die();
	}else{
		echo json_encode(array("result"=> 0));
		die();
	}
}
add_action('wp_ajax_nopriv_amumu_sns_delete_comment','amumu_sns_delete_comment');
add_action('wp_ajax_amumu_sns_delete_comment','amumu_sns_delete_comment');

function amumu_sns_more_comment(){
	
	global $wpdb, $amumu_sns_facebook;

	//$amumu_sns_facebook = isset($_SESSION['amumu_sns_facebook']) ? $_SESSION['amumu_sns_facebook'] : null;
	$offset = $_POST['page_offset'].",10";
	$output_reply = "";

	$comment_post_ID = $_POST['page_ID'];
	$wpdb->show_errors();
	$data = $wpdb->get_results(
		"SELECT comment.*, meta.meta_value AS 'comment_pic'
		FROM wp_comments AS `comment`
		JOIN
		wp_commentmeta AS meta
		ON comment.comment_ID = meta.comment_id
		WHERE comment.comment_post_ID = ".$comment_post_ID." and comment.comment_approved = 1 ORDER BY comment_date_gmt DESC LIMIT ".$offset
	);
	if(sizeof($data) == 0){ 
		echo "nohave";
		die();
	}else{
		for ($i=0; $i < sizeof($data); $i++) {
			$output_reply .= '<div class="viewarea"><span class="uimg"><img src="' .$data[$i]->comment_pic. '" alt="썸네일" /></span>';
			$output_reply .= '<div class="conwrap">';
			$output_reply .= '<div class="snscontents"><img src="'.WP_PLUGIN_URL.'/amumu-board/image/ico_mark.png" class="mark" alt="" />';
			$output_reply .= '<div class="uinfo"><img src="'.WP_PLUGIN_URL.'/amumu-board/image/'.$data[$i]->comment_type.'.gif" alt="f" />'.$data[$i]->comment_author.'<span>'.$data[$i]->comment_date_gmt.'</span>';
			if($amumu_sns_facebook == $data[$i]->comment_agent || current_user_can('manage_options')){
			$output_reply .= '<a class="replreport" value="'.$data[$i]->comment_ID.'"><img src="'.WP_PLUGIN_URL.'/amumu-board/image/ico_del.png" alt="" />삭제</a>';
			}
			$output_reply .= '</ul></div><p>';
			$output_reply .= amumu_sns_nl2br($data[$i]->comment_content);
			$output_reply .= '</p></div></div></div>';
		}
		echo $output_reply;
		die();
	}
}

add_action('wp_ajax_nopriv_amumu_sns_more_comment','amumu_sns_more_comment');
add_action('wp_ajax_amumu_sns_more_comment','amumu_sns_more_comment');

function amumu_sns_logout(){
	
	unset($_SESSION['amumu_sns_facebook']);
	unset($_SESSION['amumu_sns_name']);
	unset($_SESSION['amumu_sns_email']);
	unset($_SESSION['amumu_sns_type']);
	unset($_SESSION['amumu_sns_pic']);
	unset($_SESSION['amumu_sns_url']);

	$result = WP_PLUGIN_URL."/amumu-board/image/img_ssome.gif";

	if($result){
		echo json_encode(array("result"=> 1, "url"=> $result, "amumu_sns_facebook"=> $_SESSION['amumu_sns_facebook']));
		die();
	}else{
		echo json_encode(array("result"=> 0));
		die();
	}
}
add_action('wp_ajax_nopriv_amumu_sns_logout','amumu_sns_logout');
add_action('wp_ajax_amumu_sns_logout','amumu_sns_logout');

function amumu_sns_login(){

	$amumu_sns_type = $_POST['amumu_sns_type'];
	$amumu_sns_id = $_POST['amumu_sns_id'];
	$amumu_sns_name = $_POST['amumu_sns_name'];
	$amumu_sns_email = $_POST['amumu_sns_email'];
	$amumu_sns_pic = $_POST['amumu_sns_pic'];
	$amumu_sns_url = $_POST['amumu_sns_url'];

	if($amumu_sns_type == 'facebook') {
		$_SESSION['amumu_sns_type'] = $amumu_sns_type;
		$_SESSION['amumu_sns_facebook'] = $amumu_sns_id;
		$_SESSION['amumu_sns_name'] = $amumu_sns_name;
		$_SESSION['amumu_sns_email'] = $amumu_sns_email;
		$_SESSION['amumu_sns_pic'] = $amumu_sns_pic;
		$_SESSION['amumu_sns_url'] = $amumu_sns_url;
	}

	if(isset($_SESSION['amumu_sns_facebook'])){
		echo json_encode(array("result"=> 1));
		die();
	}else{
		echo json_encode(array("result"=> 0));
		die();
	}
}
add_action('wp_ajax_nopriv_amumu_sns_login','amumu_sns_login');
add_action('wp_ajax_amumu_sns_login','amumu_sns_login');

function amumu_sns_page_list(){
	global $wpdb;
	$page_list = $wpdb->get_results("SELECT post_title,ID FROM wp_posts WHERE post_type='page'");
	$output = "<select name='amumu_sns_add_page'>\n";
	$output .= "\t<option value=\"\">SELECT PAGE</option>\n";
	for ($i=0; $i <sizeof($page_list); $i++) {
		$output .= "\t<option value=\"" . $page_list[$i]->ID . "\">".$page_list[$i]->post_title."</option>\n";
	}
	$output .= "</select>\n";

	echo $output;
}

function amumu_getImg($content) {
	$img = "";
	preg_match("<img [^<>]*>", $content, $imgTag);
	
	if($imgTag[0]){ 
		if( stristr($imgTag[0], "http://") ) {
			preg_match("/http:\/\/.*\.(jp[e]?g|gif|png)/Ui", $imgTag[0], $imgName);
			$img = $imgName[0];
		} else {
			preg_match("/.*\.(jp[e]?g|gif|png)/Ui", $imgTag[0], $imgName);
			$img = $imgName[0];
		}
	}
	/*
	if($imgTag) {
		if( stristr($imgTag[2], "http://") ) {
			preg_match("/http:\/\/.*\.(jp[e]?g|gif|png)/Ui", $imgTag[2], $imgName);
			$img = $imgName[0];
		} else {
			preg_match("/.*\.(jp[e]?g|gif|png)/Ui", $imgTag[2], $imgName);
			$img = $imgName[0];
		}
	}
	*/
	return $img;
}

function amumu_sns_excerpt_max_charlength($charlength) {
        global $post,$wpdb;
        $content = $post->post_content;
        $excerpt = sanitize_text_field($content);
        $charlength++;

        if ( mb_strlen( $excerpt ) > $charlength ) {
                $subex = mb_substr( $excerpt, 0, $charlength - 5 );
                $exwords = explode( ' ', $subex );
                $excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
                if ( $excut < 0 ) {
                        $excerpt =  mb_substr( $subex, 0, $excut );
                } else {
                        $excerpt = $subex;
                }
                return $excerpt."[...]";
        } else {
                return $excerpt;
        }
}

function amumu_sns_content($post_ID){
	global $wpdb, $amumu_sns_facebook;
	$data = $wpdb->get_results(
		"SELECT comment.*, meta.meta_value AS 'comment_pic'
		FROM wp_comments AS `comment`
		JOIN
		wp_commentmeta AS meta
		ON comment.comment_ID = meta.comment_id
		WHERE comment.comment_post_ID = ".$post_ID." and comment.comment_approved = 1 ORDER BY comment_date_gmt DESC LIMIT 10"
	);

	for ($i=0; $i < sizeof($data); $i++) {
		$output_reply .= '<div class="viewarea"><span class="uimg"><a href="'.$data[$i]->comment_author_url.'" target="_blank"><img src="' .$data[$i]->comment_pic. '" alt="썸네일" /></a></span>';
		$output_reply .= '<div class="conwrap">';
		$output_reply .= '<div class="snscontents"><img src="'.WP_PLUGIN_URL.'/amumu-board/image/ico_mark.png" class="mark" alt="" />';
		$output_reply .= '<div class="uinfo"><img src="'.WP_PLUGIN_URL.'/amumu-board/image/'.$data[$i]->comment_type.'.gif" alt="f" /><a href="'.$data[$i]->comment_author_url.'" target="_blank">'.$data[$i]->comment_author.'</a><br/><span>'.$data[$i]->comment_date_gmt.'</span>';
		if($amumu_sns_facebook == $data[$i]->comment_agent || current_user_can('manage_options')){
			$output_reply .= '<a class="replreport" value="'.$data[$i]->comment_ID.'"><img src="'.WP_PLUGIN_URL.'/amumu-board/image/ico_del.png" alt="" />삭제</a>';
		}
		$output_reply .= '</ul></div><p>';
		$output_reply .= amumu_sns_nl2br($data[$i]->comment_content);
		$output_reply .= '</p></div></div></div>';
	}

	return $output_reply;
}

function amumu_sns_comment_count($comment_post_ID){
	global $wpdb;
	$result = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID = ".$comment_post_ID." and comment_approved = 1;" ) );

	echo $result;
}

function amumu_sns_nl2br($string){
	$string = str_replace("\n", "<br />", $string);
	if(preg_match_all('/\<pre\>(.*?)\<\/pre\>/', $string, $match)){
		foreach($match as $a){
			foreach($a as $b){
			$string = str_replace('<pre>'.$b.'</pre>', "<pre>".str_replace("<br />", "", $b)."</pre>", $string);
			}
		}
	}
	return $string;
}

function amumu_sns_close_wp_comments($comments) {
	return null;
}

add_filter('comments_array','amumu_sns_close_wp_comments');

function amumu_sns_set_wp_comment_status ( $posts ) {
	$options = get_option('amumu_sns_options');

	$allow_page = explode(",",$options['allow_page']);
	$post_ID = $posts[0]->ID;
	$is_allow = in_array($post_ID,$allow_page);

	if($is_allow){
		if ( ! empty( $posts ) && is_singular() ) {
				$posts[0]->comment_status = 'open';
				$posts[0]->post_status = 'open';
		}
	}

	return $posts;
}

add_filter( 'the_posts', 'amumu_sns_set_wp_comment_status' );

function amumu_board_head() {
	echo '<link href="'.WP_PLUGIN_URL.'/amumu-board/amumu-board.css" rel="stylesheet" type="text/css" />';
	echo '<script type="text/javascript">var ajaxurl="'.admin_url( 'admin-ajax.php', 'relative' ).'";</script>';
	echo '<script type="text/javascript" src="'.WP_PLUGIN_URL.'/amumu-board/amumu-board.js"></script>';
}

function amumu_sns_head() {
	$options = get_option( 'amumu_sns_options' );

	if ( empty( $options['facebook_app_id'] ) )
		return;

	$args = apply_filters( 'amumu_sns_init', array(
		'appId' => $options['facebook_app_id'],
		//'channelUrl' => add_query_arg( 'fb-channel-file', 1, site_url( '/' ) ),
		'channelUrl' => '//'+window.location.hostname+'/channel',
		'status' => true,
		'cookie' => true,
		'xfbml' => true
	) );

	echo '<link href="'.WP_PLUGIN_URL.'/amumu-board/amumu-sns.css" rel="stylesheet" type="text/css" />';
	echo '<div id="fb-root"></div>';
	echo '<script type="text/javascript">
			(function(d, s, id){
				 var js, fjs = d.getElementsByTagName(s)[0];
				 if (d.getElementById(id)) {return;}
				 js = d.createElement(s); js.id = id;
				 js.src = "//connect.facebook.net/en_US/all.js";
				 fjs.parentNode.insertBefore(js, fjs);
			}(document, "script", "facebook-jssdk"));
		</script>';
	echo '<script type="text/javascript">window.fbAsyncInit=function(){FB.init(' . json_encode( $args ) . ');';
	do_action( 'fb_async_init', $args );
	echo "FB.Event.subscribe('auth.authResponseChange', function(response) {
			if (response.status === 'connected') {
				// user has auth'd your app and is logged into Facebook
				var me_deferred = jQuery.Deferred();
				var pic_deferred = jQuery.Deferred();
				var me_data, pic_data;
				FB.api('/me', function(response) {
					//console.log('Good to see you, ' + response.name + '.');
					me_data = response;
					jQuery('#amumu-sns-facebook-authorId').val(response.id);
					jQuery('#amumu-sns-comment-author').val(jQuery('#amumu-sns-facebook-authorId').val());
					jQuery('#amumu-sns-facebook-name').val(response.name);
					jQuery('#amumu-sns-facebook-profile-link').val(response.link);
					jQuery('#amumu-sns-selected').val('facebook');
					me_deferred.resolve();
				});

				FB.api('/me/picture', function(response) {
					pic_data = response;
					jQuery('#amumu-sns-pic').css('background','url('+response.data.url+')');
					jQuery('#amumu-sns-facebook-pic').val(response.data.url);
					pic_deferred.resolve();
				});
				
				jQuery.when(me_deferred, pic_deferred).then(function(){
					amumu_sns_login('facebook',me_data.id,me_data.name,me_data.email,pic_data.data.url,me_data.link);
				});
				
				//jQuery('#amumu-sns-title').html('댓글은 또 다른 나 입니다.');
				jQuery('#amumu-sns-title').html('');
				jQuery('#amumu-sns-facebook-login').removeClass('off');
				jQuery('#amumu-sns-facebook-login').addClass('on');
				jQuery('#amumu-sns-text').val('');
				jQuery('#amumu-sns-text').focus();

			} else {
				// user has not auth'd your app, or is not logged into Facebook
				
				//jQuery('#amumu-sns-title').html('로그인 후 작성 가능합니다.');
				amumu_sns_logout();
				//jQuery('#amumu-sns-pic').css('background','url('img_some.gif')');
				jQuery('#amumu-sns-facebook-login').removeClass('on');
				jQuery('#amumu-sns-facebook-login').addClass('off');
				
			}
		})}</script>";
	echo '<script type="text/javascript" src="'.WP_PLUGIN_URL.'/amumu-board/amumu-sns.js"></script>';

	//add_action( 'wp_footer', 'fb_root' );
}
?>
