<?php
/**
*
* phpBB Twitch Interface [English]
*
* @package language
* @version $Id: info_mod_twitch_interface.php 100 05:30 11/04/2013 IBurn36360
* @translator IBurn36360
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
    // Globals
    'MOD_TWITCH_INTERFACE_NAME' => 'Twitch Interface',

    // Status (Live state)
    'MOD_TWITCH_INTERFACE_IS_LIVE' => 'is live.',
    'MOD_TWITCH_INTERFACE_IS_OFFLINE' => 'is offline.',
    
    // UCP
    'MOD_TWITCH_INTERFACE_ADD_AUTHORIZATION'          => 'Allow our site to access your <a href="http://www.twitch.tv">Twitch.tv</a> account.',
    'MOD_TWITCH_INTERFACE_ADD_AUTHORIZATION_DETAIL'   => 'We will only request access to your account that we need to have for our services and you may revoke that access at any time through this panel.',
    'MOD_TWITCH_INTERFACE_REVOKE_AUTHRIZATION'        => 'Revoke our sites access to your <a href="http://www.twitch.tv">Twitch.tv</a> account.',
    'MOD_TWITCH_INTERFACE_REVOKE_AUTHRIZATION_DETAIL' => 'We will remove your access code from our database.  You may reauthorize us at any time by clicking the authorize button at any time.',
    
    // ACP [FOUNDER]
    'MOD_TWITCH_INTERFACE_CLIENT_SECRET' => 'Client secret',
    'MOD_TWITCH_INTERFACE_CLIENT_KEY'    => 'Client Key',
    'MOD_TWITCH_INTERFACE_CLIENT_URI'    => 'Client URI', // This will be predetermined, but whatever
    
    // ACP (scope grants) [FOUNDER]
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_USER_READ'                         => 'user_read',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_USER_READ_DETAIL'                  => 'Requests access to non-public information about the user account.  Allows access to user email address',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_USER_BLOCKS_EDIT'                  => 'user_blocks_edit',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_USER_BLOCKS_EDIT_DETAIL'           => 'Requests access to edit the blocks list of a user.  Allows addition and removal of blocked users.',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_USER_BLOCKS_READ'                  => 'user_blocks_read',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_USER_BLOCKS_READ_DETAIL'           => 'Requests access to read the blocks list of a user.',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_USER_FOLLOWS_EDIT'                 => 'user_follows_edit',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_USER_FOLLOWS_EDIT_DETAIL'          => 'Requests access to edit the follows list of a user.  Allows the addition and removal of followed channels.',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_CHANNEL_READ'                      => 'channel_read',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_CHANNEL_READ_DETAIL'               => 'Requests access to read the non-public information of a channel.  Allows access to channel email and stream key.',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_CHANNEL_EDITOR'                    => 'channel_editor',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_CHANNEL_EDITOR_DETAIL'             => 'Requests access to change channel meta data.  Allows access to edit channel Title, Game and stream delay.',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_CHANNEL_COMMERCIAL'                => 'channel_commercial',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_CHANNEL_COMMERCIAL_DETAIL'         => 'Requests access to run a commercial on a channel.',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_CHANNEL_STREAM'                    => 'channel_stream',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_CHANNEL_STREAM_DETAIL'             => 'Requests access to reset the stream key of a channel.',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_CHANNEL_SUBSCRIPTIONS'             => 'channel_subscriptions',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_CHANNEL_SUBSCRIPTIONS_DEATIL'      => 'Requests access to read the complete list of subscribers to a channel.',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_USER_SUBSCRIPTIONS'                => 'user_subscriptions',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_USER_SUBSCRIPTIONS_DETAIL'         => 'Requests access to read the subscriptions list of a user.',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_CHANNEL_CHECK_SUBSCRIPTION'        => 'channel_check_subscription',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_CHANNEL_CHECK_SUBSCRIPTION_DETAIL' => 'Requests access to check to see if a user is subscribed to the target channel [Channel side permission].',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_CHAT_LOGIN'                        => 'chat_login',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPE_CHAT_LOGIN_DETAIL'                 => 'Requests access to generate chal login tokens for IRC.',
    
    // ACP [ADMIN-COMMON]
    'MOD_TWITCH_INTERFACE_SAVE_CHANGES'       => 'Save Changes',
    'MOD_TWITCH_INTERFACE_GRANT_SCOPES'       => 'Manage the list of requested scopes',
    'MOD_TWITCH_INTERFACE_READ_ERROR_LOG'     => 'Error log',
    'MOD_TWITCH_INTERFACE_READ_OUTPUT_LOG'    => 'Output log',
    'MOD_TWITCH_INTERFACE_READ_LOG_TIME'      => 'Time',
    'MOD_TWITCH_INTERFACE_READ_LOG_OUTPUT'    => 'Ouput',
    'MOD_TWITCH_INTERFACE_READ_LOG_ERRSTR'    => 'Error',
    'MOD_TWITCH_INTERFACE_READ_LOG_ERRNO'     => 'Error Number',
    'MOD_TWITCH_INTERFACE_CONFIRM_QUESTION'   => 'Are you sure you wish to perform this action?',
    'MOD_TWITCH_INTERFACE_CONFIRM_ACCEPT'     => 'Yes',
    'MOD_TWITCH_INTERFACE_CONFIRM_DENY'       => 'No',
    'MOD_TWITCH_INTERFACE_PURGE_CACHE'        => 'Purge Live Cache',
    'MOD_TWITCH_INTERFACE_PURGE_ACCESS_CODES' => 'Purge Access Codes',
    'MOD_TWITCH_INTERFACE_PURGE_OUTPUT_LOG'   => 'Purge Output Logs',
    'MOD_TWITCH_INTERFACE_PURGE_ERROR_LOG'    => 'Purge Error Logs',
    
    // Log events
    'MOD_TWITCH_INTERFACE_LOG_TABLE_ADD'           => 'Table %1$s has been added to the database.',
    'MOD_TWITCH_INTERFACE_LOG_TABLE_DEL'           => 'Table %1$s has been removed from the database.',
    'MOD_TWITCH_INTERFACE_LOG_TABLE_DATA_INSERT'   => 'Table %1$s has been populated with data.',
    'MOD_TWITCH_INTERFACE_LOG_MOD_ADD'             => 'Mod %1$s has been installed.',
    'MOD_TWITCH_INTERFACE_LOG_MOD_UPDATE'          => 'Mod %1$s has been updated to version %2$s.',
    'MOD_TWITCH_INTERFACE_LOG_MOD_DEL'             => 'Mod %1$s has been uninstalled.',
    'MOD_TWITCH_INTERFACE_LOG_CONFIG_ADD'          => 'Config value (%1$s) added.',
    'MOD_TWITCH_INTERFACE_LOG_CONFIG_DEL'          => 'Config value (%1$s) removed.',
    'MOD_TWITCH_INTERFACE_LOG_CONFIG_UPDATE'       => 'Config value (%1$s) updated.',
    'MOD_TWITCH_INTERFACE_LOG_CLIENT_ID_ADD'       => 'Client ID added.',
    'MOD_TWITCH_INTERFACE_LOG_CLIENT_ID_DEL'       => 'Client ID removed.',
    'MOD_TWITCH_INTERFACE_LOG_CLIENT_SECRET_ADD'   => 'Cliet Secret added.',
    'MOD_TWITCH_INTERFACE_LOG_CLIENT_SECRET_DEL'   => 'Client secret removed.',
    'MOD_TWITCH_INTERFACE_LOG_CACHE_PURGED'        => '%1$s cache purged.',
    'MOD_TWITCH_INTERFACE_LOG_ACCESS_CODES_PURGED' => 'All access codes purged from database.',
    'MOD_TWITCH_INTERFACE_LOG_OUTPUT_LOG_PURGED'   => 'Output log purged.',
    'MOD_TWITCH_INTERFACE_LOG_ERROR_LOG_PURGED'    => 'Error log purged',
    'MOD_TWITCH_INTERFACE_LOG_STARING_CRON'        => 'Starting CRON cycle.',
    'MOD_TWITCH_INTERFACE_LOG_GET_LIVE'            => 'Getting status of monitored channels.',
    'MOD_TWITCH_INTERFACE_LOG_FOLLOW_ADD'          => 'Adding %1$s as a followed channel to %2$s.',
    'MOD_TWITCH_INTERFACE_LOG_FOLLOW_DEL'          => 'Removing %1$s as a followed channel to %2$s.',
    
    // Perms
//    'MOD_TWITCH_INTERFACE_' => '',

    // ************ //
    // Installation //
    // ************ //

    // User Feedback
    'MOD_TWITCH_INTERFACE_INSTALL_INSTALLED'                      => 'This mod has been successfully installed onto your board.',
    'MOD_TWITCH_INTERFACE_INSTALL_UNINSTALL'                      => 'You can uninstall %1$s Version: %2$s by <a href="%3$s">clicking here.</a>',
    'MOD_TWITCH_INTERFACE_INSTALL_FINISHED'                       => '%1$s Version: %2$s has been installed on your forum board.  Please go into your <a href="%3$s">ACP</a> to configure the mod.',
    'MOD_TWITCH_INTERFACE_INSTALL_NOT_ADMIN'                      => 'You do not currently have the proper permission to access this script.',
    'MOD_TWITCH_INTERFACE_INSTALL_NOT_INSTALLED'                  => 'To install %1$s, please <a href="%2$s">click here.</a>',
    'MOD_TWITCH_INTERFACE_INSTALL_INSTALL'                        => '%1$s is not installed on this phpBB installation or the configuration key has been deleted from your database.',
    'MOD_TWITCH_INTERFACE_INSTALL_REINSTALL'                      => 'To reinstall %1$s, please <a href="%2$s">click here.</a>',
    'MOD_TWITCH_INTERFACE_INSTALL_CURRENT_VERSION_INSTALLED'      => 'Current version of %1$s is installed.',
    'MOD_TWITCH_INTERFACE_INSTALL_UNRECOGNIZED_VERSION_INSTALLED' => 'Unrecognized version found in database. Version %1$s was found in database.',
    'MOD_TWITCH_INTERFACE_INSTALL_UNINSTALLED'                    => '%1$s has been successfully uninstalled from your board.',
    'MOD_TWITCH_INTERFACE_INSTALL_BOARD_INDEX_REDIRECT'           => '<a href="%1$s">Click here</a> to return to your forum board.',

    'MOD_TWITCH_INTERFACE_INSTALL_' => '',
    'MOD_TWITCH_INTERFACE_INSTALL_' => '',
    'MOD_TWITCH_INTERFACE_INSTALL_' => '',
    'MOD_TWITCH_INTERFACE_INSTALL_' => '',
    'MOD_TWITCH_INTERFACE_INSTALL_' => '',
    'MOD_TWITCH_INTERFACE_INSTALL_' => '',
    'MOD_TWITCH_INTERFACE_INSTALL_' => '',
    'MOD_TWITCH_INTERFACE_INSTALL_' => '',
    'MOD_TWITCH_INTERFACE_INSTALL_' => '',
    'MOD_TWITCH_INTERFACE_INSTALL_' => '',
    
    // Errors
    'MOD_TWITCH_INTERFACE_ERROR_NO_MODE' => 'No mode supplied to installation, redirecting to main installation page in 5 seconds.',
));


?>