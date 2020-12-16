<?php
  /*
  Plugin Name: Products management
  description: >-
  a plugin to manage products
  Version: 1.1
  Author: Alexandra Nadova
  */

  if (!class_exists("ProductsPlugin")) {
      class ProductsPlugin
      {
          public $db;
          /**
          * Constructor
          */
          public function __construct()
          {
              global $wpdb;
              $this->db = $wpdb;
              /** Set the activation hook for a plugin. */
              register_activation_hook(__FILE__, array($this,'prod_createProductsTable'));
              /** add products page to admin menu */
              add_action('admin_menu', array($this, 'prod_addAdminPageContent'));
          }

          /**
           * function hooked to the 'activate_PLUGIN' action
           * serves to create DB tables when the plugin is activated
           */
          public function prod_createProductsTable()
          {
              $charset_collate = $this->db->get_charset_collate();
              $table_name = $this->db->prefix . 'products';
              $sql = "CREATE TABLE `$table_name` (
                  `product_id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` varchar(220) NOT NULL,
                  `description` varchar(220) DEFAULT NULL,
                  `price` double NOT NULL,
                  PRIMARY KEY(product_id)
                  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
              ";
              if ($this->db->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                  dbDelta($sql);
              }
          }
          /** Add a top-level menu page. */
          public function prod_addAdminPageContent()
          {
              add_menu_page('Products', 'Products', 'manage_options', __FILE__, array($this,'prod_crudProductsPage'), 'dashicons-wordpress');
          }

          /**
           * create products CRUD page with functionalities
           */
          public function prod_crudProductsPage()
          {
              // global $this->db;
              $table_name = $this->db->prefix . 'products';
              /** if button#newsubmit is pressed - creating employee */
              if (isset($_POST['newsubmit'])) {
                  $name = $_POST['newname'];
                  $description = $_POST['newdescription'];
                  $price = $_POST['newprice'];
                  $this->db->query("INSERT INTO $table_name(name,description,price) VALUES('$name','$description','$price')");
                  echo "<script>location.replace('admin.php?page=productsPlugin/index.php');</script>";
              }
              /** if button#uptsubmit is pressed - updating employee */
              if (isset($_POST['uptsubmit'])) {
                  $id = $_POST['uptid'];
                  $name = $_POST['uptname'];
                  $description = $_POST['uptdescription'];
                  $price = $_POST['uptprice'];
                  $this->db->query("UPDATE $table_name SET name='$name',description='$description', price='$price' WHERE product_id='$id'");
                  echo "<script>location.replace('admin.php?page=productsPlugin/index.php');</script>";
              }
              /**
               * delete btn adds the product_id param to url
               * deleting employee
               * if product_id param is set, delete query is executed
               */
              if (isset($_GET['del'])) {
                  $del_id = $_GET['del'];
                  $this->db->query("DELETE FROM $table_name WHERE product_id='$del_id'");
                  echo "<script>location.replace('admin.php?page=productsPlugin/index.php');</script>";
              } ?>
              <!-- using default WordPress CSS classes to design the table -->
              <div class="wrap">
                <h2>Manage products</h2>
                <table class="wp-list-table widefat striped">
                  <thead>
                    <tr>
                      <th width="25%">Product ID</th>
                      <th width="25%">Name</th>
                      <th width="25%">Description</th>
                      <th width="25%">Price</th>
                      <th width="25%">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <form action="" method="post">
                      <tr>
                        <td><input type="text" value="AUTO_GENERATED" disabled></td>
                        <td><input type="text" id="newname" name="newname"></td>
                        <td><input type="text" id="newdescription" name="newdescription"></td>
                        <td><input type="text" id="newprice" name="newprice"></td>
                        <td><button id="newsubmit" name="newsubmit" type="submit">INSERT</button></td>
                      </tr>
                    </form>
                    <?php
                    /** fetch records from db anc create rows in a table */
                      $result = $this->db->get_results("SELECT * FROM $table_name");
              foreach ($result as $print) {
                  echo "
                      <tr>
                        <td width='25%'>$print->product_id</td>
                        <td width='25%'>$print->name</td>
                        <td width='25%'>$print->description</td>
                        <td width='25%'>$print->price</td>
                        <td width='25%'>
                          <a href='admin.php?page=productsPlugin/index.php&upt=$print->product_id'>
                            <button type='button'>UPDATE</button>
                          </a>
                          <a href='admin.php?page=productsPlugin/index.php&del=$print->product_id'>
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
                      $result = $this->db->get_results("SELECT * FROM $table_name WHERE product_id='$upt_id'");
                      foreach ($result as $print) {
                          $name = $print->name;
                          $description = $print->description;
                          $price = $print->price;
                      }
                      echo "
                    <table class='wp-list-table widefat striped'>
                      <thead>
                        <tr>
                          <th width='25%'>Product ID</th>
                          <th width='25%'>Name</th>
                          <th width='25%'>Description</th>
                          <th width='25%'>Price</th>
                          <th width='25%'>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <form action='' method='post'>
                          <tr>
                            <td width='25%'>$print->product_id <input type='hidden' id='uptid' name='uptid' value='$print->product_id'></td>
                            <td width='25%'><input type='text' id='uptname' name='uptname' value='$print->name'></td>
                            <td width='25%'><input type='text' id='uptdescription' name='uptdescription' value='$print->description'></td>
                            <td width='25%'><input type='text' id='uptprice' name='uptprice' value='$print->price'></td>
                            <td width='25%'>
                              <button id='uptsubmit' name='uptsubmit' type='submit'>UPDATE</button>
                              <a href='admin.php?page=productsPlugin/index.php'>
                                <button type='button'>CANCEL</button>
                              </a>
                            </td>
                          </tr>
                        </form>
                      </tbody>
                    </table>";
                  } ?>
              </div>
              <?php
          }
      }
      $wp_plugin_template = new ProductsPlugin();
  }
