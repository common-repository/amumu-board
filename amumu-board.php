<?php
/**
 * @package Amumu-Board
 * @version 1.2.4
 */
/*
Plugin Name: Amumu-Board
Plugin URI: http://www.amumu.kr/plugins/
Description: 워드프레스 게시판 플러그인.
Author: Amumu
Author URI: http://www.amumu.kr
Version: 1.2.4
License: GPL2
*/

define('AMUMU_BOARD_VERSION','1.2.4');
define('AMUMU_BOARD_UPDATE_URL','http://www.amumu.kr/amumu_board.xml');
define('AMUMU_BOARD_UPLOAD_DIR', plugin_dir_path(__FILE__)."uploads/" );

global $amumu_board_plugin_directory;
$amumu_board_plugin_directory = dirname(__FILE__);

require_once( $amumu_board_plugin_directory . '/amumu-board-core.php' );
// Activating?
register_activation_hook(__FILE__ ,'amumu_board_activate');

register_deactivation_hook(__FILE__, 'amumu_deactivation');

register_uninstall_hook(__FILE__, 'amumu_uninstall');
