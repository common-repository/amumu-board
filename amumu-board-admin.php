<?php
	if($_POST['submit_type'] == "reset_board" && $_POST['amb_reset_board'] != ''){
		$data = $wpdb->get_results("SELECT id FROM wp_amumu_board WHERE category = ".$_POST['amb_reset_board'],"ARRAY_N" );

		for ($i=0; $i <sizeof($data); $i++) {
			if($i == 0) $reply_id_list .= $data[$i][0];
			else $reply_id_list .= ",".$data[$i][0];
		}
		$wpdb->query("DELETE FROM wp_amumu_board WHERE id in (".$reply_id_list.")");
		$wpdb->query("DELETE FROM wp_amumu_board_reply WHERE parent_id in (".$reply_id_list.")");
	}


?>

<div id="amumu-board-admin">
	<div class="amumu-board-icon" id="amumu-board-icon"></div>
	<h2><?php _e( "Amumu Board Admin", 'amumu_board' ); ?></h2>
	<?php echo amumu_board_list();?>
	<hr />
	<div>
	<h2><?php _e( "게시판 초기화", 'amumu_board' ); ?></h2>
	<p>
		게시판 초기화 방법 : 셀렉트 박스에서 게시판을 선택하고 초기화 버튼을 누른다.<br />
		*주의* 삭제된 게시판은 복구 되지 않습니다.
	</p>
		<form method="post">
		<?php echo amumu_board_list_admin(); ?>
		<input type="hidden" name="submit_type" value="reset_board" />
		<input type="submit" class="button-primary" value="<?php _e('게시판 초기화') ?>"/>
	</form>
	</div>
</div>

	