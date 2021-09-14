<?php

function eideasyLog($message) {
    if (!get_option('smartid_debug_mode')) {
        return;
    }
    $url = "https://id.eideasy.com/confirm_progress?message=" . urlencode($message);
    wp_remote_get($url);
};
