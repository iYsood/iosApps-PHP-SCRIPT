<?php
    $config = array(
        // title for the page
        "title" => "iYsood OTA",

        // Parses each plist file
        "parsePlist" => true,

        // Base local path for scanning for plist/ipa files
        "localPath" => ".",

        // Base url for the plist/ipa files with trailing slash. Not needed if parsePlist == true.
        "baseUrl" => "https://localhost/iosApps"
    );

    $base_url = $config["baseUrl"];

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

?>