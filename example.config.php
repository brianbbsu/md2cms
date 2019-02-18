<?php
    // Database config (PostgreSQL database used by CMS)
    define('DB',Array(
        "host" => "localhost",
        "port" => 5432,
        "dbname" => "cmsdb",
        "user" => "cmsuser",
        "password" => "123456"
    ));

    // HackMD URL of each task (referenced by task number)
    // Key should be a number starting from 1.
    define('HACKMD_URL',Array(
        "1" => "https://hackmd.io/dOtHmWtOTF-V59hZJ5HfFw",
        "2" => "https://hackmd.io/G1OIal8OSbiPqj-ftpcJkA"
    ));
?>
