<div
    id="<?php echo esc_attr($id) ?>"
    class="login-button"
>
    <?php echo wp_kses_post(apply_filters($filterName, '<img class="login-middle-w" src="' . esc_url($imageSrc) . '">')) ?>
</div>
