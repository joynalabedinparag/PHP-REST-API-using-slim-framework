<?php
defined('SM_APEXEC') or define('SM_APEXEC', '1');
$system_file_path = __DIR__ . '/system_settings.php';
if (is_file($system_file_path)) {
    require $system_file_path;
} else {
    die("System File Not Found");
}

$db_conn = mysqli_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
    die('DB Connect Error');
}

$settings = [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        // Database Settings
        'db' =>[
            'con' =>$db_conn,
        ],

    ],
];

return $settings;
?>