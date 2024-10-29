<?php
	if($_POST['amumu_board_submit'] == "submit"){

		if($_POST['is_allow'] == 1) $is_allow = true;
		else $is_allow = false;

		$amumu_board_options = get_option('amumu_board_options');

		if($_POST['amumu_board_add']){
			if($_POST['is_category_page'] != ''){
				$_POST['is_category_page'] = $_POST['is_category_page'].','.$_POST['amumu_board_add'];
			}else{
				$_POST['is_category_page'] = $_POST['amumu_board_add'];
			}
		}

		if($_POST['amumu_board_add_nowrite']){
			if($_POST['no_write'] != ''){
				$_POST['no_write'] = $_POST['no_write'].','.$_POST['amumu_board_add_nowrite'];
			}else{
				$_POST['no_write'] = $_POST['amumu_board_add_nowrite'];
			}
		}

		$amumu_board_options = array('is_allow' 	=> $is_allow,
					'per_page' => $_POST['per_page'],
					'is_category_page' => $_POST['is_category_page'],
					'no_write' => $_POST['no_write'],
					);
		update_option('amumu_board_options', $amumu_board_options);
		$message .= __( "Options saved.", 'amumu_board' );
	}else{
		$amumu_board_options = get_option('amumu_board_options');
	}
?>
<style>
.amumu-board-form-table th {
	text-align:left;
	padding:4px 10px 8px;
}
.amumu-board-form-table td {
	padding:4px 10px 8px;
}
</style>
<div id="amumu-board-settins">
	<div class="amumu-board-icon" id="amumu-board-icon"></div>
	<h2><?php _e( "Amumu Board Options", 'amumu_board' ); ?></h2>
	<form method="post">
		<span style="margin-bottom:15px;">
			<p><?php _e( "If you would like to add a Amumu Board to your website, just create page and save the settings.", 'amumu_board' ); ?></p>
		</span>
		<table class="amumu-board-form-table">
			<tr valign="top">
				<th scope="row" style="width:195px;"><?php _e( "Number per page", 'amumu_board' ); ?></th>
				<td colspan="2">
					<input type="text" style="width:200px;" name="per_page" value="<?php echo stripslashes( $amumu_board_options['per_page'] ); ?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" style="width:195px;"><?php _e( "Enable Amumu Board", 'amumu_board' ); ?></th>
				<td>
					<input type="checkbox" id="cntctfrm_display_add_info" name="is_allow" value="1" <?php if($amumu_board_options['is_allow']) echo "checked=\"checked\" "; ?>/>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" style="width:195px;"><?php _e( "Board List", 'amumu_board' ); ?></th>
				<td colspan="2">
					<input type="text" style="width:200px;" name="is_category_page" value="<?php echo stripslashes( $amumu_board_options['is_category_page'] ); ?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" style="width:195px;"><?php _e( "Add Board", 'amumu_board' ); ?></th>
				<td colspan="2">
					<?php
						amumu_board_page_list();
					?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" style="width:195px;"><?php _e( "No Write", 'amumu_board' ); ?></th>
				<td colspan="2">
					<input type="text" style="width:200px;" name="no_write" value="<?php echo stripslashes( $amumu_board_options['no_write'] ); ?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" style="width:195px;"><?php _e( "Add No Write", 'amumu_board' ); ?></th>
				<td colspan="2">
					<?php
						amumu_board_nowrite_list();
					?>
				</td>
			</tr>
			
		</table>    
		<input type="hidden" name="amumu_board_submit" value="submit" />
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
</div>