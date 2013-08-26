<?php

define('__EVISPA_IP', '213.197.132.246');
$__clientIp = getenv('REMOTE_ADDR');
define('__IS_DEV_SERVER', in_array($__clientIp, array('127.0.0.1', '192.168.1.153')));
define('__IS_CLIENT_EVISPA', ($__clientIp == __EVISPA_IP) || __IS_DEV_SERVER);

if ( !__IS_CLIENT_EVISPA ) {
    die('Permission denied.');
}

define('__SYMFONY_PATH', __DIR__.'/..');

function delete_files($path, $del_dir = FALSE, $level = 0, $exclude = array()) {
    // Trim the trailing slash
    $path = rtrim($path, DIRECTORY_SEPARATOR);

    if (!$current_dir = @opendir($path))
        return;

    while (FALSE !== ($filename = @readdir($current_dir))) {
    // Ignore hidden folders
        if (!in_array($filename, $exclude) and substr($filename, 0, 1) != '.') {
            if (is_dir($path . DIRECTORY_SEPARATOR . $filename)) {
                delete_files($path . DIRECTORY_SEPARATOR . $filename, $del_dir, $level + 1, $exclude);
            } else {
                @unlink($path . DIRECTORY_SEPARATOR . $filename);
            }
        }
    }
    @closedir($current_dir);

    if ($del_dir == TRUE AND $level > 0) {
        @rmdir($path);
    }
}

function _out($t, $ic = 0) {
    for ($j = 0; $j < $ic; $j++) {
        echo '&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    echo $t;
    echo '<br />'.PHP_EOL;
}

delete_files(__SYMFONY_PATH . '/app/cache', TRUE);
_out('deleted files in '.__SYMFONY_PATH . '/app/cache');

if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    apc_clear_cache('user');
    apc_clear_cache('opcode');
    _out('cleared all apc cache');
}

