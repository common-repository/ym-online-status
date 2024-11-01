<?php
/*
Plugin Name: Yahoo Messenger Online Status
Plugin URI: http://hudatoriq.web.id/wp-hacks/ym-online-status
Description: A plugin that creates icons/buttons that show your Yahoo Messenger Online Status.
Author: Huda Toriq
Version: 0.4-beta
Author URI: http://hudatoriq.web.id/
*/

define("YM_STATUS_BASE_DIR", "ym-online-status/");
define("YM_STATUS_DIR", "wp-content/plugins/" . YM_STATUS_BASE_DIR);
define("YM_STATUS_IMG_DIR", YM_STATUS_DIR . "images/");

/* Define the extensions of image supported by this plugin. File formats of which
extension is not defined here will not show up in the YM Status Options Menu,
even if the file exists in the /images directory */

class ymstatus {
	var $supported_image_extension = array('jpg','jpeg','png','gif','bmp');
	var $options = array();
	var $note = '';

	function activate() {
		$this->setdefault();
		header("Location: admin.php?page=ym_status");
		exit;
	}

	function setdefault() {
		$options = array(
			"ids" => array(),
			"button" => "smile.png",
			"img_title" => "",
			"widget" => array(),
			"tags" => array(
				"single-button-open" => "<p>",
				"single-button-close" => "</p>",
				"multiple-list-open" => "<div>",
				"multiple-list-close" => "</div>",
				"multiple-button-open" => "<p>",
				"multiple-button-close" => "</p>"
			)
		);
		update_option('ym_status', $options);
	}

	function deactivate() {
		//Clear settings from wp_options when deactivating this plugin, keeping your database clean.
		delete_option('ym_status');
	}

	function get_options() {
		if(empty($this->options)) {
			$this->options = get_option('ym_status');
		}
	}

	function ym_status_menu() {
		load_plugin_textdomain('ym_status', YM_STATUS_DIR);
		add_options_page(__('YM Status', 'ym_status'), __('YM Status', 'ym_status'), 9, 'ym_status', array(& $this, 'ym_status_option'));
	}

	function is_supported_image($image) {
		$supported = false;
		$fileinfo = pathinfo($image);
		foreach($this->supported_image_extension as $value) {
			if($fileinfo['extension'] == $value) {
				$supported = true;
			}
		}
		return $supported;
	}

	function ym_status_option() {
		$this->get_options();
		load_plugin_textdomain('ym_status', YM_STATUS_DIR);
		$dir = "../" . YM_STATUS_IMG_DIR;

		if ($handle = opendir($dir)) {
			while (false !== ($filename = readdir($handle))) {
				if ($filename != "." && $file != "..") {
					$filepath = "../" . YM_STATUS_IMG_DIR . $filename;
					if(is_file($filepath)) {
						if($this->is_supported_image($filepath)) {
							if(substr($filename, 0, 7) == 'online-') {
								$online[] = $filename;
							} elseif(substr($filename, 0, 8) == 'offline-') {
								$offline[] = $filename;
							}
						}
					}
				}
			}
			closedir($handle);
		}
		$pairs = count($online);
		$ym_ids = implode(" ", $this->options['ids']);
		if(!$ym_ids) {
			$this->note = __('You haven&#8217;t specified any Yahoo Messenger ID.', 'ym_status');
		}
		$ym_title = $this->options['img_title'];
		$ym_selected_button = $this->options['button'];

		if($this->note) {
			?>
			<div id="message" class="updated fade"><p><strong><?php echo($this->note); ?></strong></p></div>
			<?php
		}
		?>
		<div class="wrap">
			<h2><?php _e('Yahoo Messenger Online Status', 'ym_status'); ?></h2>
			<form method="post" action="">
			<p class="submit"><input type="submit" name="ym_submit" value="<?php _e('Update Options &raquo;','ym_status') ?>" /></p>
			<style type="text/css">
			.serverinfo {
				background-color: #CFEBF7;
				border: 1px solid #2580B2;
				padding: 1em;
				width: 50%;
				margin: 1em auto 10px;
			}
			.serverinfo_error {
				background-color:	#FFEFF7;
				border: 1px solid #CC6699;
				padding: 1em;
				width: 50%;
				margin: 1em auto 10px;

			}
			.serverinfo p, .serverinfo_error p {
				text-align: center;
			}
			</style>
			<?php
			$img_enabled = get_bloginfo('url') . '/' . YM_STATUS_DIR . 'accept.png';
			$img_disabled = get_bloginfo('url') . '/' . YM_STATUS_DIR . 'error.png';
			$msg_enabled = __('Enabled', 'ym_status');
			$msg_disabled = __('Disabled', 'ym_status');
			if(!ini_get('allow_url_fopen')) {
				$url_fopen = False;
				$url_fopen_msg = $msg_disabled;
				$url_fopen_img = $img_disabled;
			} else {
				$url_fopen = True;
				$url_fopen_msg = $msg_enabled;
				$url_fopen_img = $img_enabled;
			}
			if(!function_exists('curl_init')) {
				$curl = False;
				$curl_msg = $msg_disabled;
				$curl_img = $img_disabled;
			} else {
				$curl = True;
				$curl_msg = $msg_enabled;
				$curl_img = $img_enabled;
			}
			if($url_fopen && $curl || $url_fopen) {
				$setting_msg = __('YM-Online-Status should work fine in this server. It will use PHP&#8217;s <code>file_get_contents()</code> function to retrieve your status from Yahoo! server.', 'ym_status');
				$serverinfo_class = "serverinfo";
			} elseif($curl) {
				$setting_msg = __('YM-Online-Status should work fine in this server. It will use PHP&#8217;s cURL library functions to retrieve your status from Yahoo! server.', 'ym_status');
				$serverinfo_class = "serverinfo";
			} else {
				$setting_msg = __('Sorry. Your server does not support any methods which are needed by YM-Online-Status plugin to retrieve your status from Yahoo! server.', 'ym_status');
				$serverinfo_class = "serverinfo_error";
			}
			?>
			<div class="<?php echo $serverinfo_class; ?>">
				<table align="center" class="" width="70%" cellspacing="2" cellpadding="5">
					<tr>
						<th colspan="3" align="center"><?php _e('Server Settings', 'ym_status'); ?></th>
					</tr>
					<tr valign="top">
						<td align="right"><?php _e('allow_url_fopen', 'ym_status'); ?>:</td>
						<td><?php echo $url_fopen_msg; ?></td>
						<td><img src="<?php echo $url_fopen_img; ?>" alt="" /></td>
					</tr>
					<tr valign="top">
						<td align="right"><?php _e('cURL Support', 'ym_status'); ?>:</td>
						<td><?php echo $curl_msg; ?></td>
						<td><img src="<?php echo $curl_img; ?>" alt="" /></td>
					</tr>
				</table>
				<p><?php echo $setting_msg; ?></p>
			</div>
			<fieldset class="options">
			<legend><?php _e('Plugin Generals', 'ym_status'); ?></legend>
			<table class="optiontable editform" width="100%" cellspacing="2" cellpadding="5">
				<tr valign="top">
					<th scope="row"><?php _e('Yahoo Messenger ID(s)', 'ym_status'); ?>:</th>
					<td colspan="3"><input name="yahoo_id" type="text" id="yahoo_id" value="<?php echo($ym_ids) ?>" size="40" class="code" /><br />
					<small><?php _e('Define the Yahoo! ID(s) of which online status will be displayed. Separate multiple values with spaces.', 'ym_status'); ?></small></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Text', 'ym_status'); ?>:</th>
					<td colspan="3"><input name="button_title" type="text" id="button_title" value="<?php echo($this->options['img_title']) ?>" size="40" class="code" /><br />
					<small><?php _e('Specify the text value of title and alt attributes of the button image. It will show up when you hover the pointer on the button image.','ym_status'); ?></small></td>
				</tr>
					<tr>
						<th><?php _e('Sort buttons by:', 'ym_status'); ?></th>
						<td colspan="3">
						<?php
						$selected = ' selected="selected"';
						$select = array();
						switch($this->options['id_order']) {
							case 'ascending':
								$select['ascending'] = $selected;
								break;
							case 'descending':
								$select['descending'] = $selected;
								break;
							default:
								$select['input'] = $selected;
						}
						?>
							<select name="id_order">
								<option<?php echo($select['input']); ?> value="input"><?php _e('input order', 'ym_status'); ?></option>
								<option<?php echo($select['ascending']); ?> value="ascending"><?php _e('Yahoo! id, ascending', 'ym_status'); ?></option>
								<option<?php echo($select['descending']); ?> value="descending"><?php _e('Yahoo! id, descending', 'ym_status'); ?></option>
							</select><br /><small>Used when you display multiple statuses as a list</small>
						</td>
					</tr>
				<tr valign="top">
					<th scope="row" rowspan="<?php echo($pairs + 1); ?>"><?php _e('Status Button:', 'ym_status'); ?></th>
					<td>&nbsp;</td>
					<td><?php _e('Name','ym_status') ?></td>
					<td><?php _e('Online','ym_status') ?></td>
					<td><?php _e('Offline','ym_status') ?></td>
				</tr>
					<?php
					$a = 1;
					foreach($online as $filename) {
						$fileinfo = pathinfo($filename);
						$name = substr($filename, 7);
						if($name == $ym_selected_button) {
							$checked = ' checked="checked"';
						} else {
							$checked = '';
						}
						?>

				<tr>
					<td><?php echo($a); ?></td>
					<td><label><input type="radio" name="ym_button_choice" value="<?php echo($name); ?>"<?php echo($checked) ?> /> <?php echo($name); ?></label></td>
					<td><img src="<?php echo("../" . YM_STATUS_IMG_DIR . $filename); ?>" alt="" /></td>
					<td><img src="<?php echo("../" . YM_STATUS_IMG_DIR . "offline-" . $name); ?>" alt="" /></td>
				</tr>
						<?php
						$a ++;
					}
					?>
				</tr>
			</table>
			</fieldset>
			<fieldset class="options">
				<legend><?php _e('Widget Preferences - Single Status', 'ym_status'); ?></legend>
				<table class="optiontable editform" width="100%" cellspacing="2" cellpadding="5">
					<tr>
						<th><?php _e('Button wrapper tags :', 'ym_status'); ?></th>
						<td><input type="text" size="5" name="single-button-open-tag" id="single-button-open-tag" value="<?php echo($this->options['tags']['single-button-open']); ?>" /> and <input type="text" size="5" name="single-button-close-tag" id="single-button-close-tag" value="<?php echo($this->options['tags']['single-button-close']); ?>" /> <small><?php _e('For example: <strong><code>&lt;p&gt; and &lt;/p&gt;</code></strong> or <strong><code>&lt;div&gt; and &lt;/div&gt;</code></strong>', 'ym_status'); ?></small></td>
					</tr>
				</table>
			</fieldset>
			<fieldset class="options">
				<legend><?php _e('Widget Preferences - Multiple (List) Status', 'ym_status'); ?></legend>
				<table class="optiontable editform" width="100%" cellspacing="2" cellpadding="5">
					<tr>
						<th><?php _e('List wrapper tags:', 'ym_status'); ?></th>
						<td><input type="text" size="5" name="multiple-list-open-tag" id="multiple-list-open-tag" value="<?php echo($this->options['tags']['multiple-list-open']); ?>" /> <?php _e('and', 'ym_status'); ?> <input type="text" size="5" name="multiple-list-close-tag" id="multiple-list-close-tag" value="<?php echo($this->options['tags']['multiple-list-close']); ?>" /> <small><?php _e('For example: <strong><code>&lt;div&gt; and &lt;/div&gt;</code></strong> or <strong><code>&lt;ul&gt; and &lt;/ul&gt;</code></strong>', 'ym_status'); ?></small></td>
					</tr>
					<tr>
						<th><?php _e('Button wrapper tags:', 'ym_status'); ?></th>
						<td><input type="text" size="5" name="multiple-button-open-tag" id="multiple-button-open-tag" value="<?php echo($this->options['tags']['multiple-button-open']); ?>" /> <?php _e('and', 'ym_status'); ?> <input type="text" size="5" name="multiple-button-close-tag" id="multiple-button-close-tag" value="<?php echo($this->options['tags']['multiple-button-close']); ?>" /> <small><?php _e('For example: <strong><code>&lt;p&gt; and &lt;/p&gt;</code></strong> or <strong><code>&lt;li&gt; and &lt;/li&gt;</code></strong>', 'ym_status'); ?></small></td>
					</tr>
				</table>
			</fieldset>
			<p class="submit"><input type="submit" name="ym_submit" value="<?php _e('Update Options &raquo;','ym_status') ?>" /></p>
			</form>
		</div>
		<?php
	}

	function show_single($button = '', $ymid = '') {
		$this->get_options();
		$wp_root = get_bloginfo('wpurl') . "/";
		$append_ymid = '';
		$append_button = '';
		$append = false;
		$append_string = '';
		if(!$ymid) {
			$ymid = $this->options[ids][0]; 
		} else {
			$append = true;
			$append_string .= '?ymid=' . $ymid;
		}
		if($button) {
			$append_string .= ($append) ? '&' : '?';
			$append_string .= 'button=' . $button;
			$append = true;
		}
		?>
		<a href="ymsgr:sendim?<?php echo($ymid) ?>" title="<?php echo($this->options['img_title']) ?>" class="ym_button"><img src="<?php echo($wp_root . YM_STATUS_DIR . "image.php" . $append_string) ?>" alt="YM Status" /></a>
		<?php
	}
	function show_list($button = '', $widget = false) {
		$this->get_options();
		switch ($this->options['id_order']) {
			case 'ascending':
				sort($this->options['ids']);
				break;
			case 'descending':
				rsort($this->options['ids']);
				break;
		}
		if($widget) {
			echo $this->options['tags']['multiple-list-open'];
			foreach($this->options['ids'] as $id) {
				echo($this->options['tags']['multiple-button-open']);
				$this->show_single('', $id);
				echo($this->options['tags']['multiple-button-close']);
			}
			echo $this->options['tags']['multiple-list-close'];
		} else {
			echo '<ul class="ymstatus_list">';
			foreach($this->options['ids'] as $id) {
				echo "<li>";
				$this->show_single($button, $id);
				echo "</li>";
			}
			echo '</ul>';
		}
	}

	function update_options() {
		load_plugin_textdomain('ym_status', YM_STATUS_DIR);
		$this->get_options();
		$newvalue = $this->options;

		$ym_ids = strtolower(trim($_POST['yahoo_id']));
		$ym_id_array = explode(" ", $ym_ids);
		$button_title = stripslashes(htmlspecialchars(trim($_POST['button_title'])));
		$button_choice = $_POST['ym_button_choice'];

		$newvalue['ids'] = $ym_id_array;
		$newvalue['img_title'] = $button_title;
		$newvalue['button'] = $button_choice;
		$newvalue['tags']['single-button-open'] = $_POST['single-button-open-tag'];
		$newvalue['tags']['single-button-close'] = $_POST['single-button-close-tag'];
		$newvalue['tags']['multiple-list-open'] = $_POST['multiple-list-open-tag'];
		$newvalue['tags']['multiple-list-close'] = $_POST['multiple-list-close-tag'];
		$newvalue['tags']['multiple-button-open'] = $_POST['multiple-button-open-tag'];
		$newvalue['tags']['multiple-button-close'] = $_POST['multiple-button-close-tag'];
		$newvalue['id_order'] = $_POST['id_order'];
		if($this->options != $newvalue) {
			update_option('ym_status', $newvalue);
			$this->note = __('You have successfully updated your YM status plugin options.', 'ym_status');
		} else {
			$this->note = __('You have nothing to update', 'ym_status');
		}
	}

	function ymstatus_widget_init() {
		if(function_exists('wp_register_sidebar_widget')) {
			wp_register_sidebar_widget('ym-online-status', __('YM Status'), array(& $this, 'ymstatus_widget'));
			wp_register_widget_control('ym-online-status', __('YM Status'), array(& $this, 'ymstatus_widget_control'));
		}
	}

	function ymstatus_widget_control() {
		$this->get_options();
		if($_POST['ym-online-status-widget-submit']) {
			$newvalue = $this->options['widget'];
			$newvalue['widget_title'] = strip_tags(stripslashes($_POST['ym-online-status-title']));
			$newvalue['type'] = $_POST["ym-online-status-type"];
			if($newvalue != $this->options['widget']) {
				$this->options['widget'] = $newvalue;
				update_option('ym_status', $this->options);
			}

		}
			$selected = ' selected="selected"';
		if($this->options['widget']['type'] == 'list') {
			$select['list'] = $selected;
			$select['single'] = '';
		} else {
			$select['list'] = '';
			$select['single'] = $selected;
		}

		?>
		<p><label for="ym-online-status-title"><?php _e('Title:', 'ym_status'); ?> <input style="width: 100px;" id="ym-online-status-title" name="ym-online-status-title" type="text" value="<?php echo $this->options['widget']['widget_title']; ?>" /></label></p>
		<p><label for="ym-online-status-type"><?php _e('Widget mode:', 'ym_status'); ?> <select style="width: 100px" name="ym-online-status-type" id="ym-online-status-type"><option<?php echo $select['single']; ?> value="single"><?php _e('Single', 'ym_status'); ?></option><option<?php echo $select['list']; ?> value="list"><?php _e('List', 'ym_status'); ?></option></select></label></p>
		<input type="hidden" id="ym-online-status-widget-submit" name="ym-online-status-widget-submit" value="1" />
		<?php
	}

	function ymstatus_widget($args) {
		$this->get_options();
		$options = $this->options['widget'];
		extract($args);
		echo $before_widget;
		echo $before_title . $options['widget_title'] . $after_title;
		if($this->options['widget']['type'] == 'list') {
			$this->show_list();
		} else {
			$this->options['tags']['single-button-open'];
			$this->show_single();
			$this->options['tags']['single-button-close'];
		}
		echo $after_widget;
	}

	function init() {
		if(is_admin()) {
			if($_POST['ym_submit']) {
				$this->update_options();
			}
		}
	}
	
	function is_wpuser_ym($ym_id = '') {
		if(!$ym_id) {
			return false;
		}
		global $wpdb;
		$wpusers_ym = $wpdb->get_col("SELECT LCASE(meta_value) FROM $wpdb->usermeta WHERE meta_key='yim' GROUP BY 'meta_value'");
		if(in_array($ym_id, $wpusers_ym)) {
			return true;
		}
		return false;
	}
	
	function is_specified_ym($ym_id = '') {
		$this->get_options();
		if(!$ym_id) {
			return false;
		}
		if(in_array($ym_id, $this->options['ids'])) {
			return true;
		}
		return false;
	}

	function ymstatus() {
		add_action('activate_ym-online-status/ym_status.php',array(& $this, 'activate'));
		add_action('deactivate_ym-online-status/ym_status.php',array(& $this, 'deactivate'));
		add_action('init', array(& $this, 'init'));
		add_action('admin_menu', array(& $this, 'ym_status_menu'));
		add_action('widgets_init', array(& $this, 'ymstatus_widget_init'));
	}
}

$ymstatus = new ymstatus();

/* deprecated in 0.3 */
function get_ym_status() {
	global $ymstatus;
	$ymstatus->show_single();
}
/* These are the template tags */
function the_author_ymstatus($button = '') {
	global $ymstatus;
	$yim = get_the_author_yim();
	if($yim) { 
		$ymstatus->show_single($button, get_the_author_yim());
	}
}

function ymstatus_single($button = '') {
	global $ymstatus;
	$ymstatus->show_single($button);
}

function ymstatus_list($button = '') {
	$ymstatus->show_list($button);
}

?>