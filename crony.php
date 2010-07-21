<?php
/*
Plugin Name: Crony Cronjob Manager
Plugin URI: http://www.scottkclark.com/
Description: Create and Manage Cronjobs in WP by running Scripts, Functions, and/or PHP code. This plugin utilizes the wp_cron API.
Version: 0.1.2
Author: Scott Kingsley Clark
Author URI: http://www.scottkclark.com/
*/

global $wpdb;
define('CRONY_TBL',$wpdb->prefix.'crony_');
define('CRONY_VERSION','012');
define('CRONY_URL',WP_PLUGIN_URL.'/crony');
define('CRONY_DIR',WP_PLUGIN_DIR.'/crony');

add_action('admin_init','crony_init');
add_action('admin_menu','crony_menu');

function crony_init ()
{
    global $current_user,$wpdb;
    // check version
    $version = get_option('crony_version');
    if(empty($version)||$version=='010')
    {
        // thx pods ;)
        $sql = file_get_contents(CRONY_DIR.'/assets/dump.sql');
        $sql_explode = preg_split("/;\n/", str_replace('wp_', $wpdb->prefix, $sql));
        if(count($sql_explode)==1)
            $sql_explode = preg_split("/;\r/", str_replace('wp_', $wpdb->prefix, $sql));
        for ($i = 0, $z = count($sql_explode); $i < $z; $i++)
        {
            $wpdb->query($sql_explode[$i]);
        }
        delete_option('crony_version');
        add_option('crony_version',CRONY_VERSION);
    }
    elseif($version!=CRONY_VERSION)
    {
        delete_option('crony_version');
        add_option('crony_version',CRONY_VERSION);
    }
    // thx gravity forms, great way of integration with members!
    $capabilities = crony_capabilities();
    if ( function_exists( 'members_get_capabilities' ) ){
        add_filter('members_get_capabilities', 'crony_get_capabilities');
        if(current_user_can("crony_full_access"))
            $current_user->remove_cap("crony_full_access");
        $is_admin_with_no_permissions = current_user_can("administrator") && !crony_current_user_can_any(crony_capabilities());
        if($is_admin_with_no_permissions)
        {
            $role = get_role("administrator");
            foreach($capabilities as $cap)
            {
                $role->add_cap($cap);
            }
        }
    }
    else
    {
        $crony_full_access = current_user_can("administrator") ? "crony_full_access" : "";
        $crony_full_access = apply_filters("crony_full_access", $crony_full_access);

        if(!empty($crony_full_access))
            $current_user->add_cap($crony_full_access);
    }
}
function crony_menu ()
{
    global $wpdb;
    $has_full_access = current_user_can('crony_full_access');
        $has_full_access = true;
    if(!$has_full_access&&current_user_can('administrator'))
        $has_full_access = true;
    $min_cap = crony_current_user_can_which(crony_capabilities());
    if(empty($min_cap))
        $min_cap = 'crony_full_access';
    add_menu_page('Cronjobs', 'Cronjobs', $has_full_access ? 'read' : $min_cap, 'crony', null, CRONY_URL.'/assets/icons/16.png');
    add_submenu_page('crony', 'Manage Cronjobs', 'Manage Cronjobs', $has_full_access ? 'read' : 'crony_manage', 'crony', 'crony_manage');
    //add_submenu_page('crony', 'View Logs', 'View Logs', $has_full_access ? 'read' : 'crony_manage', 'crony-logs', 'crony_logs');
    //add_submenu_page('crony', 'Settings', 'Settings', $has_full_access ? 'read' : 'crony_settings', 'crony-settings', 'crony_settings');
    add_submenu_page('crony', 'About', 'About', $has_full_access ? 'read' : $min_cap, 'crony-about', 'crony_about');
}
function crony_get_capabilities ($caps)
{
    return array_merge($caps,crony_capabilities());
}
function crony_capabilities ()
{
    return array('crony_full_access','crony_settings','crony_manage');
}
function crony_current_user_can_any ($caps)
{
    if(!is_array($caps))
        return current_user_can($caps) || current_user_can("crony_full_access");
    foreach($caps as $cap)
    {
        if(current_user_can($cap))
            return true;
    }
    return current_user_can("crony_full_access");
}
function crony_current_user_can_which ($caps)
{
    foreach($caps as $cap)
    {
        if(current_user_can($cap))
            return $cap;
    }
    return "";
}

function crony_settings ()
{
?>
<div class="wrap">
    <div id="icon-edit-pages" class="icon32" style="background-position:0 0;background-image:url(<?php echo CRONY_URL; ?>/assets/icons/32.png);"><br /></div>
    <h2>Crony Cronjob Manager - Settings</h2>
    <div style="height:20px;"></div>
    <link  type="text/css" rel="stylesheet" href="<?php echo CRONY_URL; ?>/assets/admin.css" />
    <form method="post" action="">
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label>Reset Crony</label></th>
                <td>
                    <input name="clear" type="submit" id="clear" value=" Clear Now " />
                    <span class="description">This will remove all cronjobs from Crony</span>
                </td>
            </tr><!--
            <tr valign="top">
                <th scope="row"><label for=""></label></th>
                <td>
                    <input name="" type="text" id="" value="0" class="small-text" />
                    <span class="description"></span>
                </td>
            </tr>-->
        </table><!--
        <p class="submit">
            <input type="submit" name="Submit" class="button-primary" value="  Save Changes  " />
        </p>-->
    </form>
</div>
<?php
}
function crony_manage ()
{
    require_once CRONY_DIR.'/wp-admin-ui/Admin.class.php';
    $columns = array('name','disabled'=>array('Disabled?','type'=>'bool'),'start'=>array('label'=>'Start Running On','custom_input'=>'crony_start_input','type'=>'date'),'schedule'=>array('custom_display'=>'crony_schedule_display','custom_input'=>'crony_schedule_input'),'created'=>array('label'=>'Date Created','type'=>'date'),'updated'=>array('label'=>'Last Modified','type'=>'date'));
    $form_columns = $columns;
    $form_columns['script'] = array('label'=>'Script to Include','comments'=>'Path to Script or URL to Script (if server configuration supports it) for include','comments_top'=>true);
    $form_columns['function'] = array('label'=>'Function to Run');
    $form_columns['phpcode'] = array('label'=>'Custom PHP to Run','type'=>'desc','comments'=>'PHP Tag is already initiated, code away!','comments_top'=>true);
    $form_columns['created']['date_touch_on_create'] = true;
    $form_columns['created']['display'] = false;
    $form_columns['updated']['date_touch'] = true;
    $form_columns['updated']['display'] = false;
    $admin = new WP_Admin_UI(array('css'=>CRONY_URL.'/assets/admin.css','item'=>'Cronjob','items'=>'Cronjobs','table'=>CRONY_TBL.'jobs','columns'=>$columns,'form_columns'=>$form_columns,'icon'=>CRONY_URL.'/assets/icons/32.png'));
    $admin->go();
}
function crony_schedule_display ($column,$data,$obj)
{
    $schedules = wp_get_schedules();
    return $schedules[$column]['display'];
}
function crony_schedule_input ($column,$attributes,$obj)
{
    $schedules = wp_get_schedules();
    $interval = array();
    foreach ($schedules as $key => $value)
    {
        $interval[$key]  = $value['interval'];
    }
    array_multisort($interval,SORT_NUMERIC,$schedules);
?>
<select name="<?php echo $column; ?>">
<?php
    foreach($schedules as $id=>$schedule)
    {
?>
    <option value="<?php echo $id; ?>"<?php echo ($obj->row[$column]==$id?' SELECTED':''); ?>><?php echo $schedule['display']; ?></option>
<?php
    }
?>
</select>
<?php
}
function crony_start_input ($column,$attributes,$obj)
{
    $obj->row[$column] = empty($obj->row[$column]) ? date("Y-m-d H:i:s") : $obj->row[$column];
?>
<script type="text/javascript" src="<?php echo CRONY_URL; ?>/assets/date_input.js"></script>
<link type="text/css" rel="stylesheet" href="<?php echo CRONY_URL; ?>/assets/date_input.css" />
<script type="text/javascript">
jQuery(function() {
    jQuery(".wp_admin_ui input.date").date_input();
});
</script>
<input type="text" name="<?php echo $column; ?>" value="<?php echo $obj->row[$column]; ?>" class="regular-text date" />
<?php
}
function crony_about ()
{
?>
<div class="wrap">
    <div id="icon-edit-pages" class="icon32" style="background-position:0 0;background-image:url(<?php echo CRONY_URL; ?>/assets/icons/32.png);"><br /></div>
    <h2>About the Crony Cronjob Manager plugin</h2>
    <div style="height:20px;"></div>
    <link  type="text/css" rel="stylesheet" href="<?php echo CRONY_URL; ?>/assets/admin.css" />
    <table class="form-table about">
        <tr valign="top">
            <th scope="row">About the Plugin Author</th>
            <td><a href="http://www.scottkclark.com/">Scott Kingsley Clark</a> from <a href="http://skcdev.com/">SKC Development</a>
                <span class="description">Scott specializes in WordPress and Pods CMS Framework development using PHP, MySQL, and AJAX. Scott is also a developer on the <a href="http://podscms.org/">Pods CMS Framework</a> plugin and has a creative outlet in music with his <a href="http://www.softcharisma.com/">Soft Charisma</a></span></td>
        </tr>
        <tr valign="top">
            <th scope="row">Official Support</th>
            <td><a href="http://www.scottkclark.com/forums/crony-cronjob-manager/">Crony Cronjob Manager - Support Forums</a></td>
        </tr>
        <tr valign="top">
            <th scope="row">Features</th>
            <td>
                <ul>
                    <li><strong>Administration</strong>
                        <ul>
                            <li>Create and Manage Cronjobs</li>
                            <li>Admin.Class.php - A class for plugins to manage data using the WordPress UI appearance</li>
                        </ul>
                    </li>
                    <li><strong>API</strong>
                        <ul>
                            <li>Add a job via the Crony API through other plugins</li>
                        </ul>
                    </li>
                </ul>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Upcoming Features - Roadmap</th>
            <td>
                <dl>
                    <dt>0.2</dt>
                    <dd>
                        <ul>
                            <li>Still deciding</li>
                        </ul>
                    </dd>
                </dl>
            </td>
        </tr>
    </table>
    <div style="height:50px;"></div>
</div>
<?php
}

add_action('crony','crony',10,1);
add_filter('cron_schedules','crony_schedules',10,2);
if(is_admin()&&isset($_GET['page'])&&strpos($_GET['page'],'crony')!==false)
{
    add_action('wp_admin_ui_post_save','crony_add_job',10,2);
    add_action('wp_admin_ui_post_delete','crony_remove_job',10,2);
}
function crony ($id)
{
    global $wpdb;
    $row = @current($wpdb->get_results('SELECT * FROM '.CRONY_TBL.'jobs WHERE `disabled`=0 AND `id`='.$wpdb->_real_escape($id),ARRAY_A));
    if(false===$row)
        return false;
    ob_start();
    if(0<strlen($row['script']))
        include_once $row['script'];
    if(0<strlen($row['function'])&&function_exists("{$row['function']}"))
        $row['function']();
    if(0<strlen($row['phpcode']))
        eval($row['phpcode']);
    $return = ob_get_clean();
    if(0<strlen($return))
        return $return;
    return true;
}
function crony_schedules ($schedules)
{
    $schedules['twicehourly'] = array( 'interval' => 1800, 'display' => __('Twice Hourly') );
    $schedules['weekly'] = array( 'interval' => 604800, 'display' => __('Once Weekly') );
    $schedules['twiceweekly'] = array( 'interval' => 302400, 'display' => __('Twice Weekly') );
    $schedules['monthly'] = array( 'interval' => 2628002, 'display' => __('Once Monthly') );
    $schedules['twicemonthly'] = array( 'interval' => 1314001, 'display' => __('Twice Monthly') );
    $schedules['yearly'] = array( 'interval' => 31536000, 'display' => __('Once Yearly') );
    $schedules['twiceyearly'] = array( 'interval' => 15768012, 'display' => __('Twice Yearly') );
    $schedules['fouryearly'] = array( 'interval' => 7884006, 'display' => __('Four Times Yearly') );
    $schedules['sixyearly'] = array( 'interval' => 5256004, 'display' => __('Six Times Yearly') );
    return apply_filters('crony_schedules',$schedules);
}
function crony_add_job ($args,$obj)
{
    if($obj[0]->table!=CRONY_TBL.'jobs')
        return false;
    if(!isset($args[3])||false===$args[3]||!isset($args[2])||empty($args[2]))
        return false;
    crony_remove_job($args,$obj);
    if($args[2]['disabled']==1)
        return true;
    $timestamp = strtotime($args[2]['start']);
    $recurrence = $args[2]['schedule'];
    return wp_schedule_event($timestamp,$recurrence,'crony',array($args[1]));
    //wp_schedule_single_event($timestamp,'crony',array());
}
function crony_remove_job ($args,$obj)
{
    if($obj[0]->table!=CRONY_TBL.'jobs')
        return false;
    $schedules = _get_cron_array();
    $timestamp = false;
	$key = md5(serialize(array($args[1])));
    foreach($schedules as $ts=>$schedule)
    {
        if(isset($schedule['crony'])&&isset($schedule['crony'][$key]))
        {
            $timestamp = $ts;
            wp_unschedule_event($timestamp,'crony',array($args[1]));
        }
    }
    if(false===$timestamp)
        return false;
    return true;
}