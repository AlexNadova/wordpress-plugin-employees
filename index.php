<?php
  /*
  Plugin Name: Employees management
  description: >-
  a plugin to manage employees
  Version: 1.0
  Author: Alexandra Nadova
  */

/** Set the activation hook for a plugin. */
register_activation_hook(__FILE__, 'createEmployeesTable');
/**
 * function hooked to the 'activate_PLUGIN' action
 * serves to create DB tables when the plugin is activated
 */
function createEmployeesTable()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'employees';
    $sql = "CREATE TABLE `$table_name` (
      `user_id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(220) DEFAULT NULL,
      `email` varchar(220) DEFAULT NULL,
      PRIMARY KEY(user_id)
      ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
    ";
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
/** add employees page to admin menu */
add_action('admin_menu', 'addAdminPageContent');
/** Add a top-level menu page. */
function addAdminPageContent()
{
    add_menu_page('Employees', 'Employees', 'manage_options', __FILE__, 'crudEmployeesPage', 'dashicons-wordpress');
}

function crudEmployeesPage()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'employees';
    /** if button#newsubmit is pressed - creating employee */
    if (isset($_POST['newsubmit'])) {
        $name = $_POST['newname'];
        $email = $_POST['newemail'];
        $wpdb->query("INSERT INTO $table_name(name,email) VALUES('$name','$email')");
        echo "<script>location.replace('admin.php?page=employeesPlugin/index.php');</script>";
    }
    /** if button#uptsubmit is pressed - updating employee */
    if (isset($_POST['uptsubmit'])) {
        $id = $_POST['uptid'];
        $name = $_POST['uptname'];
        $email = $_POST['uptemail'];
        $wpdb->query("UPDATE $table_name SET name='$name',email='$email' WHERE user_id='$id'");
        echo "<script>location.replace('admin.php?page=employeesPlugin/index.php');</script>";
    }
    /**
     * delete btn adds the user_id param to url
     * deleting employee
     * if user_id param is set, delete query is executed
     */
    if (isset($_GET['del'])) {
        $del_id = $_GET['del'];
        $wpdb->query("DELETE FROM $table_name WHERE user_id='$del_id'");
        echo "<script>location.replace('admin.php?page=employeesPlugin/index.php');</script>";
    } ?>
  <!-- using default WordPress CSS classes to design the table -->
  <div class="wrap">
    <h2>Manage employees</h2>
    <table class="wp-list-table widefat striped">
      <thead>
        <tr>
          <th width="25%">User ID</th>
          <th width="25%">Name</th>
          <th width="25%">Email Address</th>
          <th width="25%">Actions</th>
        </tr>
      </thead>
      <tbody>
        <form action="" method="post">
          <tr>
            <td><input type="text" value="AUTO_GENERATED" disabled></td>
            <td><input type="text" id="newname" name="newname"></td>
            <td><input type="text" id="newemail" name="newemail"></td>
            <td><button id="newsubmit" name="newsubmit" type="submit">INSERT</button></td>
          </tr>
        </form>
        <?php
        /** fetch records from db anc create rows in a table */
          $result = $wpdb->get_results("SELECT * FROM $table_name");
    foreach ($result as $print) {
        echo "
              <tr>
                <td width='25%'>$print->user_id</td>
                <td width='25%'>$print->name</td>
                <td width='25%'>$print->email</td>
                <td width='25%'>
                  <a href='admin.php?page=employeesPlugin/index.php&upt=$print->user_id'>
                    <button type='button'>UPDATE</button>
                  </a>
                  <a href='admin.php?page=employeesPlugin/index.php&del=$print->user_id'>
                    <button type='button'>DELETE</button>
                  </a>
                </td>
              </tr>
            ";
    } ?>
      </tbody>  
    </table>
    <br>
    <br>
    <?php
      /**
       * if update btn is pressed, fetch employee's data and
       * create update table
       */
      if (isset($_GET['upt'])) {
          $upt_id = $_GET['upt'];
          $result = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id='$upt_id'");
          foreach ($result as $print) {
              $name = $print->name;
              $email = $print->email;
          }
          echo "
        <table class='wp-list-table widefat striped'>
          <thead>
            <tr>
              <th width='25%'>User ID</th>
              <th width='25%'>Name</th>
              <th width='25%'>Email Address</th>
              <th width='25%'>Actions</th>
            </tr>
          </thead>
          <tbody>
            <form action='' method='post'>
              <tr>
                <td width='25%'>$print->user_id <input type='hidden' id='uptid' name='uptid' value='$print->user_id'></td>
                <td width='25%'><input type='text' id='uptname' name='uptname' value='$print->name'></td>
                <td width='25%'><input type='text' id='uptemail' name='uptemail' value='$print->email'></td>
                <td width='25%'><button id='uptsubmit' name='uptsubmit' type='submit'>UPDATE</button> <a href='admin.php?page=employeesPlugin/index.php'><button type='button'>CANCEL</button></a></td>
              </tr>
            </form>
          </tbody>
        </table>";
      } ?>
  </div>
  <?php
}
