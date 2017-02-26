<?php
defined('ABSPATH') or die('No script kiddies please!');
?>
<div class = "container">
    <div id = "loginBlock" class = "col-md-offset-3 col-md-6">
        <h1>Click the link below to activate the Smart ID service.</h1>
        <small>This will take you to Smart ID admin site where you can register the API keys.</small>
        <br>
        <a href="https://id.smartid.ee/admin/api_register_client<?php
        echo "?api_register=yes"
        . "&api_redirect_uri=" . home_url()
        . "&api_redirect_back=" . admin_url("admin.php?page=smart-id-settings")
        . "&api_home_url=" . home_url();
        ?>" target="_blank">Activate Smart ID login</a>
    </div>
    <h3>By registering you accept the below terms and conditions</h3>
    <div>
        <?php include("terms.html"); ?>
    </div>
</div>











