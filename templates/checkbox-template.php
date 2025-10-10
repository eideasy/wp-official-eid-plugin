<?php
// Ensure variables are defined to avoid undefined variable warnings
if (!isset($name)) $name = '';
if (!isset($id)) $id = '';
if (!isset($label)) $label = '';
if (!isset($checked)) $checked = false;
?>
<input
    type="checkbox"
    name="<?php echo esc_attr($name) ?>"
    id="<?php echo esc_attr($id) ?>"
    class="column-cb"
    value="yes"
    <?php echo $checked ? "checked" : "" ?>
>
<label for="<?php echo esc_attr($id) ?>"><?php echo esc_html($label) ?></label>
