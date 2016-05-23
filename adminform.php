Adminformis
<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
<script>tinymce.init({
        selector: 'textarea',
        plugins: "code"
    });</script>

<?php
global $wpdb;
//If contract text was submitted then deactivate previous and activate new
if (isset($_POST["contract_save"]) && $_POST["contract_save"] == 'yes') {
    
    IdContract::saveContract($_POST["contract_html"]);
}

//get current contract_html value from DB
$contract = $wpdb->get_row(
        "select * from " . $wpdb->prefix . "contract_html where active=1"
);

//Show default text if nothing saved yet
if ($contract == NULL) {
    $contract_html = "Paste your contract text here";
} else {
    $contract_html = $contract->html;
}
?>

<form name="contractSaveForm" method="post" action="">
    <input type="hidden" name="contract_save" value="yes">
    <textarea name="contract_html"><?php echo $contract_html ?></textarea>
    <button type="submit" >Save</button>
</form>
