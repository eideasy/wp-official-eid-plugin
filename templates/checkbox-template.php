<input
    type="checkbox"
    name="<?php echo esc_attr($name) ?>"
    id="<?php echo esc_attr($id) ?>"
    class="column-cb"
    value="yes"
    <?php echo $checked ? "checked" : "" ?>
>
<label for="<?php echo esc_attr($id) ?>"><?php echo esc_html($label) ?></label>
