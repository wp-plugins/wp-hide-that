<?php
if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

//add if not already there
$wpht_array=array();
$wpht_selectors=array();
add_option('wpht_idstohide',$wpht_array);
add_option('wpht_class2hide','');
add_option('wpht_selectors',$wpht_selectors);
$wpht_selectors=get_option('wpht_selectors');

$wpht_array=get_option('wpht_idstohide');
//save options
if(!empty($_POST))
  {
  foreach($_POST as $param_name => $param_val)
    {
    $id=str_replace('wp_hidetitle_','',$param_name);
    if ($param_val==='hide')
      {
      if (!in_array($id,$wpht_array))
        {
        array_push($wpht_array,$id);
        update_option('wpht_idstohide',$wpht_array);
        }
      }else if($param_val==='show'){
      if(($key = array_search($id, $wpht_array)) !== false) 
        {
        unset($wpht_array[$key]);
        update_option('wpht_idstohide',$wpht_array);
        }
      }else{
      //not show or hide, must be other option
      update_option($param_name,$param_val);
      }
    }
    echo '<div class="updated"><p><strong>Settings Saved!!</strong></p></div>'; 
  }

?>
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/jquery.dataTables.css' , __FILE__ ); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'css/dataTables.fixedHeader.css' , __FILE__ ); ?>">
<script type="text/javascript" charset="utf8" src="<?php echo plugins_url( 'js/jquery.dataTables.js' , __FILE__ ); ?>"></script>
<script type="text/javascript" charset="utf8" src="<?php echo plugins_url( 'js/dataTables.fixedHeader.js' , __FILE__ ); ?>"></script>
<script type="text/javascript">

function wpht_submit(){
document.getElementById("wpht_form").submit();
}

//hide/show all
function hide_all(){
jQuery('#wpht_form select').each(
    function(index){  
        var input = jQuery(this);
        jQuery('#'+input.attr('name')).val('hide');
    }
);
}
function show_all(){
jQuery('#wpht_form select').each(
    function(index){  
        var input = jQuery(this);
        jQuery('#'+input.attr('name')).val('show');
    }
);
}

var x = 1;
jQuery(document).ready( function () {


    //draw table
    var table=jQuery('#wpht_table').DataTable({
    "order": [[ 1, "desc" ]],
    "iDisplayLength": -1, //all    
    "bLengthChange": false,
    "bPaginate": false
    });
    
    //fixed header and footer
    new jQuery.fn.dataTable.FixedHeader( table, {
    "offsetTop": 32,
    "bottom": true
    } );
    

    
} );
</script>

<?php
//get all pages and posts
$args = array(
	'sort_order' => 'ASC',
	'sort_column' => 'post_date',
	'hierarchical' => 0,
	'exclude' => '',
	'include' => '',
	'meta_key' => '',
	'meta_value' => '',
	'authors' => '',
	'child_of' => 0,
	'parent' => -1,
	'exclude_tree' => '',
	'number' => '',
	'offset' => 0,
	'post_type' => 'page',
	'post_status' => 'publish,private'
); 
$pages = get_pages($args);
$args = array(
	'posts_per_page'   => 99999999,
	'offset'           => 0,
	'category'         => '',
	'orderby'          => 'post_date',
	'order'            => 'DESC',
	'include'          => '',
	'exclude'          => '',
	'meta_key'         => '',
	'meta_value'       => '',
	'post_type'        => 'post',
	'post_mime_type'   => '',
	'post_parent'      => '',
	'post_status'      => 'publish,private',
	'suppress_filters' => true );
$posts = get_posts($args);   
$all_p=array_merge($pages,$posts);

$wpht_array=get_option('wpht_idstohide');
?>
<div class="wrap">
  <div id="icon-themes" class="icon32"></div>
  <h2>WP-Hide That!</h2>
  <div class="postbox-container" style="width:100%">
    <div class="metabox-holder">
      <div class="meta-box">
        <div class="postbox" style="width:100%;">
          <div class="inside">
            <form name="oscimp_form" id="wpht_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
              <ul><li>-CSS Selectors can be classes or IDs. Classes must begin with a dot (.classname) and IDs must begin with a hash (#idname).</li>
              <li>-Selectors listed below in the "Global Selectors" box will apply to all pages that are set to Hide the objects.</li>
              <li>-If selectors are specified in the page or post editor then they will be used instead of the global selectors for that page only.</li>
              </ul>
              <strong>CSS Global Selectors:</strong> <input type="text" name="wpht_class2hide" value="<?php echo get_option('wpht_class2hide'); ?>" size="20" /> Separate by comma. 
              <table id="wpht_table" class="display">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Modification Date</th>
                    <th>Title</th>
                    <th>Page/Post</th>
                    <th>Current Status</th>
                    <th>Selectors</th>
                    <th>Edit</th>
                  </tr>
                </thead>
                <tbody>
                <?php
                foreach ($all_p as $key => $value) 
                  {
                  if (in_array($value->ID,$wpht_array))
                    {
                    $current_status='Hiding';
                    $wpht_isinarray=true;
                    }else{
                    $current_status='Showing';
                    $wpht_isinarray=false;
                    }
                  echo '<tr><td>'.$value->ID.'</td><td>'.$value->post_date.'</td><td>'.$value->post_title.'</td><td>'.$value->post_type.'</td><td>'.$current_status.'</td>';
                  echo '<td>';
                  if ($wpht_selectors[$value->ID])
                    {
                    echo $wpht_selectors[$value->ID];
                    }else if ($current_status=='Hiding'){
                    echo get_option('wpht_class2hide').'(global)';
                    }
                  echo '</td>';
                  echo '<td><select name="wp_hidetitle_'.$value->ID.'" id="wp_hidetitle_'.$value->ID.'">';
                  echo '<option value="show"';
                  if(!$wpht_isinarray)
                    {
                    echo ' selected';
                    }
                  echo '>Show Objects</option>';
                  echo '<option value="hide"';
                  if($wpht_isinarray)
                    {
                    echo ' selected';
                    }
                  echo '>Hide Objects</option>';
                  echo '</select>';
                  echo '</td></tr>';
                  }
                ?>
                </tbody>
                <tfoot>
                  <tr>
                    <th colspan="7" style="text-align:center"><a onclick="hide_all()" style="cursor:pointer;padding:0px 15px;">Change All Visible/Filtered To "Hide Objects"</a><input class="button-primary" type="submit" name="Submit" value="Save Settings" style="margin-bottom:2px;" onclick="wpht_submit()" /><a onclick="show_all()" style="cursor:pointer;padding:0px 15px;">Change All Visible/Filtered To "Show Objects"</a><br />Note:This only saves what is visible/filtered. If you searched for a post, then only the filtered ones will be saved. Clear the search box to remove the filter and save all.</td>
                  </tr>
                </tfoot>
              </table>              
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>              
