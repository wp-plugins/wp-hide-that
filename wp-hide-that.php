<?php
/*
Plugin Name: WP-Hide That
Plugin URI: http://njarb.com/contact-us
Description: Allows you to hide certain classes and IDs on some or all of your pages and posts. Very easy to turn on and off.
Version: 1.2
Author: Cyle Conoly
Author URI: http://cconoly.com
License: GPL2
Copyright 2014  Cyle Conoly  (email : cyle.conoly@gmail.com)
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function add_hidethat_meta_box() {
  $screens = array( 'post', 'page' );
  foreach ( $screens as $screen ) {
   add_meta_box( 
         'wp-hidethat',
         'WP-Hide That',
         'hidethat_meta_box_display',
         $screen,
         'normal',
         'low'
    );
  }
}

function hidethat_meta_box_display($post, $metabox) {
  $wpht_array=array();
  $wpht_selectros=array();
  add_option('wpht_idstohide',$wpht_array);
  add_option('wpht_class2hide','');
  add_option('wpht_selectors',$wpht_selectros);
  $wpht_array=get_option('wpht_idstohide');
  $wpht_global_sel=get_option('wpht_class2hide');
  $wpht_selectors=get_option('wpht_selectors');
  $wpht_id=get_the_ID();
  //see if in array
  if (in_array($wpht_id,$wpht_array))
    {
    $wpht_isinarray=true;
    }else{
    $wpht_isinarray=false;
    }
   
  wp_nonce_field( plugin_basename( __FILE__ ), 'wphidethat_nonce' ); 
  
  //currently...
  echo 'Currently <b>';
  if(!$wpht_isinarray)
      {
      echo ' SHOWING';
      }else{
      echo ' <strong>HIDING</strong>';
      }
  echo '</b> the objects.<br />';
    
  //input field  
  echo '<select name="wp_hidetitle">';
  echo '<option value="show"';
    if(!$wpht_isinarray)
      {
      echo ' selected';
      }
  echo '>Show Objects On This Page</option>';
  echo '<option value="hide"';
    if($wpht_isinarray)
      {
      echo ' selected';
      }
  echo '>Hide Objects On This Page</option>';
  echo '</select>';
  echo '<br /><strong>CSS Selectors For This Page Only:</strong> <input type="text" name="wp_selectors" size="20" value="'.$wpht_selectors[$wpht_id].'"/> Separate with commas. Use a dot for classes (.class) and a hash for IDs (#id). Leave blank to use the global selectors.';
  echo '<br />Current selectors: <strong>';
  if ($wpht_selectors[$wpht_id])
    {
    echo $wpht_selectors[$wpht_id];
    }else if ($wpht_global_sel){
    echo $wpht_global_sel.' (global selectors)'; 
    }else {
    echo 'none';
    }
  echo '</strong>';
    
  }
  
function hidethat_meta_box_save(){
  // check if this isn't an auto save
  //if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
  //  return;
  
  // security check
  if ( !wp_verify_nonce( $_POST['wphidethat_nonce'], plugin_basename( __FILE__ ) ) )
    return;
  
  $wpht_id=get_the_ID();
  $wpht_array=get_option('wpht_idstohide');
  $wpht_post_select=$_POST['wp_hidetitle'];
  $wpht_selectors=get_option('wpht_selectors'); 
  $wpht_post_selectors=$_POST['wp_selectors'];
   
  if( ! empty( $wpht_post_select ) ) {
    if ($wpht_post_select==='hide')
      {
      if (!in_array($wpht_id,$wpht_array))
        {
        array_push($wpht_array,$wpht_id);
        update_option('wpht_idstohide',$wpht_array);
        }
      }else{
      if(($key = array_search($wpht_id, $wpht_array)) !== false) 
        {
        unset($wpht_array[$key]);
        update_option('wpht_idstohide',$wpht_array);
        }
      }
    }
  $wpht_selectors[$wpht_id]=$wpht_post_selectors;
  update_option('wpht_selectors',$wpht_selectors);
}  

add_action( 'add_meta_boxes', 'add_hidethat_meta_box' );
add_action( 'save_post', 'hidethat_meta_box_save' );

//settings menu
function wpht_admin() {  
    include_once('wpht_options.php');  
}  
function wpht_admin_actions() {  
  add_options_page("WP-HideThat", "WP-Hide That", "manage_options", "WP-HideThat", "wpht_admin");          
  }  
add_action('admin_menu', 'wpht_admin_actions');


//add css to head
function wpht_init_head (){
$wpht_array=get_option('wpht_idstohide');
$wpht_id=get_the_ID();
$wpht_class2hide=get_option('wpht_class2hide');
$wpht_selectors=get_option('wpht_selectors');
if($wpht_id && in_array($wpht_id,$wpht_array) && $wpht_class2hide!=='' && $wpht_selectors[$wpht_id]==''){
  if (strpos(strrev($wpht_class2hide), ',') === 0)
    {
    $wpht_class2hide=substr($wpht_class2hide, 0, -1);
    }  
  $wpht_class2hide=str_replace(' ','',$wpht_class2hide);
  $wpht_class2hide=str_replace(',',', ',$wpht_class2hide);
  echo '<style type="text/css">'.$wpht_class2hide.'{display: none !important;}</style>';
}else if ($wpht_id && in_array($wpht_id,$wpht_array) && $wpht_selectors[$wpht_id]!=='')
{
  if (strpos(strrev($wpht_selectors[$wpht_id]), ',') === 0)
    {
    $wpht_selectors[$wpht_id]=substr($wpht_selectors[$wpht_id], 0, -1);
    }
  $wpht_selectors[$wpht_id]=str_replace(' ','',$wpht_selectors[$wpht_id]);
  $wpht_selectors[$wpht_id]=str_replace(',',', ',$wpht_selectors[$wpht_id]); 
  echo '<style type="text/css">'.$wpht_selectors[$wpht_id].'{display: none !important;}</style>';
}
}
add_action('wp_head', 'wpht_init_head');