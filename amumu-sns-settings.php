<?php
	if($_POST['amumu_sns_submit'] == "submit"){
		if($_POST['is_allow'] == 1) $is_allow = true;
		else $is_allow = false;

		if($_POST['amumu_sns_add_page']){
			if($_POST['allow_page'] != ''){
				$_POST['allow_page'] = $_POST['allow_page'].','.$_POST['amumu_sns_add_page'];
			}else{
				$_POST['allow_page'] = $_POST['amumu_sns_add_page'];
			}
		}

		$amumu_sns_options = array('is_allow' 	=> $is_allow,
					'facebook_app_id' => $_POST['facebook_app_id'],
					'allow_page' => $_POST['allow_page'],
					);
		update_option('amumu_sns_options', $amumu_sns_options);
		$message .= __( "Options saved.", 'amumu_sns' );
	}else{
		$amumu_sns_options = get_option('amumu_sns_options');
		if($amumu_sns_options['facebook_app_id'] ==''){
			$error = __( "Enter App ID.", 'amumu_sns' );
		}
	}
?>
<style>
.amumu-sns-form-table th {
	text-align:left;
	padding:4px 10px 8px;
}
.amumu-sns-form-table td {
	padding:4px 10px 8px;
}
</style>
<div id="amumu-sns-settins">
	<div class="amumu-sns-icon" id="amumu-sns-icon"></div>
	<h2><?php _e( "Amumu SNS Options", 'amumu_sns' ); ?></h2>
	<div class="updated fade" <?php if( ! isset( $_REQUEST['amumu_sns_submit'] ) || $error != "" ) echo "style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
		<div class="error" <?php if( "" == $error ) echo "style=\"display:none\""; ?>><p><strong><?php echo $error; ?></strong></p></div>
	<form method="post" action="admin.php?page=amumu-board/amumu-sns-settings.php">
		<span style="margin-bottom:15px;">
			<p><?php _e( "If you would like to add a Amumu SNS to your website, just fill facebook App ID and save the settings.", 'amumu_sns' ); ?></p>
		</span>
		<table class="amumu-sns-form-table">
			<tr valign="top" class="cntctfrm_additions_block">
				<th scope="row" style="width:195px;"><?php _e( "Facebook App ID", 'amumu_sns' ); ?></th>
				<td colspan="2">
					<input type="text" style="width:200px;" name="facebook_app_id" value="<?php echo stripslashes( $amumu_sns_options['facebook_app_id'] ); ?>" />
				</td>
			</tr>
			<tr valign="top" class="cntctfrm_additions_block">
				<th scope="row" style="width:195px;"><?php _e( "Enable Amumu SNS", 'amumu_sns' ); ?></th>
				<td>
					<input type="checkbox" id="cntctfrm_display_add_info" name="is_allow" value="1" <?php if($amumu_sns_options['is_allow']) echo "checked=\"checked\" "; ?>/>
				</td>
			</tr>
			<tr valign="top" class="cntctfrm_additions_block">
				<th scope="row" style="width:195px;"><?php _e( "Allow Page List", 'amumu_sns' ); ?></th>
				<td colspan="2">
					<input type="text" style="width:200px;" name="allow_page" value="<?php echo stripslashes( $amumu_sns_options['allow_page'] ); ?>" />
				</td>
			</tr>
			<tr valign="top" class="cntctfrm_additions_block">
				<th scope="row" style="width:195px;"><?php _e( "Add Page", 'amumu_newboard' ); ?></th>
				<td colspan="2">
					<?php
						amumu_sns_page_list();
					?>
				</td>
			</tr>
		</table>    
		<input type="hidden" name="amumu_sns_submit" value="submit" />
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
</div>