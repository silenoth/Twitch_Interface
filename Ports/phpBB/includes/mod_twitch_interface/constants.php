<?php
$twitchInterface_cachePath = $phpbb_root_path . '/mod_twitch_interface/cache/';

// Database tables
define('MOD_TWITCH_INTERFACE_CONFIG',          $table_prefix . 'mod_twitch_interface_config');          // Configuration information (Overrides the defauls in the file)
define('MOD_TWITCH_INTERFACE_OUTPUT_LOG',      $table_prefix . 'mod_twitch_interface_output_log');      // The output log from the interface
define('MOD_TWITCH_INTERFACE_ERROR_LOG',       $table_prefix . 'mod_twitch_interface_error_log');       // The error log from the interface
define('MOD_TWITCH_INTERFACE_CODE_CACHE',      $table_prefix . 'mod_twitch_interface_code_cache');      // The auth code cache from the interface (Works on user ID)
define('MOD_TWITCH_INTERFACE_CRON',            $table_prefix . 'mod_twitch_interface_cron');            // Cron stack and arguments used for it

// Cache path
define('MOD_TWITCH_INTERFACE_CACHE_LIVE',      $twitchInterface_cachePath . 'live.php');
?>