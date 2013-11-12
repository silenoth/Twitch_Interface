<?php

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('acp/common', 'mods/info_mod_twitch_interface'));

// Init and grab out our vars
$authorizationCode = request_var('code', '');

if ($authorizationCode == '')
{
    // Tell the user we had an issue grabbing the code, they will be able to retry in the UCP
    $template->assign_vars();
} else {
    // Make sure the user is logged in and isn't a bot
    if ($user->data['user_id'] != ANONYMOUS)
    {
        
    } else {
        // Force the login, keeping the code we grabbed from the query string
    }    
}


?>