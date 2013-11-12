<?php
/**
 * Version 1.0.0
 * 
 * Installation and update file for the phpBB port of Anthony 'IBurn36360' Diaz's
 * twitch_interface.
 */ 
 
/**
 * @todo - Finish installation logic for modules in UCP and ACP
 */ 
 
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../../../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
include($phpbb_root_path . 'install/install_main.' . $phpEx);
include($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);
include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('acp/common', 'mods/info_mod_twitch_interface'));

// Version in use
define('MOD_VERSION', '1.0.0');
define('TWITCH_INTERFACE_MOD_NAME', $user->lang['MOD_TWITCH_INTERFACE_NAME']);
$version     = 'mod_twitch_interface_version';
$mode 	     = request_var('mode', '');
$installmode = request_var('installmode', '');
$error	     = '';

// Check for admin
if (!$auth->acl_get('a_'))
{
	if ($user->data['is_bot'])
	{
		redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
	} else {
		trigger_error('MOD_TWITCH_INTERFACE_INSTALL_NOT_ADMIN');
	}
}

// Set up needed tools
include($phpbb_root_path . 'mod_twitch_interface/install/install_common.php');
require($phpbb_root_path . 'includes/mod_twitch_interface/constants.php');
$db_tools 	= new phpbb_db_tools($db);
$install    = new twitchInterfaceInstaller();
$modules 	= new acp_modules();
$auth_admin = new auth_admin();

// Set up our redirects
$url_install    = append_sid("{$phpbb_root_path}mod_twitch_interface_install/install.$phpEx", 'installmode=check');
$uninstall      = append_sid("{$phpbb_root_path}mod_twitch_interface_install/install.$phpEx", 'installmode=pass&mode=del');
$update  	    = append_sid("{$phpbb_root_path}mod_twitch_interface_install/install.$phpEx", 'installmode=pass&mode=update');
$instal_mod     = append_sid("{$phpbb_root_path}mod_twitch_interface_install/install.$phpEx", 'installmode=pass&mode=install');
$reinstal_mod   = append_sid("{$phpbb_root_path}mod_twitch_interface_install/install.$phpEx", 'installmode=pass&mode=reinstall');
$install_select = append_sid("{$phpbb_root_path}mod_twitch_interface_install/install.$phpEx", 'installmode=select');
$acp_redirect   = append_sid("{$phpbb_root_path}adm/index.$phpEx");
$board_redirect = append_sid("{$phpbb_root_path}index.$phpEx");

switch($mode)
{
    case 'check':
        // Check our dependencies, toss an error is we do and exit here, more will be added here to attempt to load the modules on runtime if they exist there later
        if (!extension_loaded('curl') || !extension_loaded('json'))
        {
            trigger_error(sprintf($user->lang['MOD_TWITCH_INTERFACE_INSTALL_MISSING_DEPENDENCY'], TWITCH_INTERFACE_MOD_NAME, MOD_VERSION, $user->lang['MOD_TWITCH_INTERFACE_INSTALL_CURL'], $user->lang['MOD_TWITCH_INTERFACE_INSTALL_JSON'], $board_redirect));
            
            // Pass everything off to the exit handler and let it exit gracefully.  Will flush the page before killing the script
            exit_handler();
        } else {
            trigger_error(sprintf($user->lang['MOD_TWITCH_INTERFACE_INSTALL_DEPENDENCY_FOUND']));
            
            meta_refresh(5, $install_select);
        }
    break;
    
    case 'select':
        // Check if current version already installed
        if (isset($config[$version]))
        {
        	if ($config[$version] == MOD_VERSION) // Current version case
        	{
        		define('CUR_VERSION', 1);
        		if (!$mode) // We should NEVER have a mode here.  If we do, someone messed with the query string
        		{
        			trigger_error(sprintf($user->lang['MOD_TWITCH_INTERFACE_INSTALL_INSTALLED'] . '<br /><br />' .  $user->lang['MOD_TWITCH_INTERFACE_INSTALL_FINISHED'], TWITCH_INTERFACE_MOD_NAME, MOD_VERSION, $acp_redirect) . '<br /><br />' . sprintf($user->lang['MOD_TWITCH_INTERFACE_INSTALL_REINSTALL'], TWITCH_INTERFACE_MOD_NAME, $reinstal_mod) . '<br /><br />' . sprintf($user->lang['MOD_TWITCH_INTERFACE_INSTALL_UNINSTALL'], TWITCH_INTERFACE_MOD_NAME, MOD_VERSION, $uninstall));
        		}
        	}
        } else {
        	define('CUR_VERSION', 0); // No version case
        	if (!$mode) // We should NEVER have a mode here.  If we do, someone messed with the query string
        	{
        		trigger_error(sprintf($user->lang['MOD_TWITCH_INTERFACE_INSTALL_NOT_INSTALLED'], TWITCH_INTERFACE_MOD_NAME) . '<br /><br />' . sprintf($user->lang['MOD_TWITCH_INTERFACE_INSTALL_INSTALL'], TWITCH_INTERFACE_MOD_NAME, $instal_mod));
        	}
        }
    break;
    
    case 'pass':
        twitchInterfaceInstall($installmode);
    break;
}

function twitchInterfaceInstall($installmode)
{
    // What are we trying to do?
    switch($installmode)
    {
        // If we run into the update case where the mod is not recognized version wise or the admin wants a reinstallation because of something
        case 'reinstall':
            // Start by completely uninstalling the mod structure in the DB
            $install->delete_config('mod_twitch_interface_version');
            unset($config[$version]);
            
            // Delete all of the added tables
            $tables = array(
               MOD_TWITCH_INTERFACE_ERROR_LOG,
               MOD_TWITCH_INTERFACE_OUTPUT_LOG,
               MOD_TWITCH_INTERFACE_CODE_CACHE,
               MOD_TWITCH_INTERFACE_CONFIG
            );
            
            $install->delete_table($tables);
            
            // Now reinstall everything to the base state
    		$config_data = array(
                'mod_twitch_interface_version' => array(MOD_VERSION, 0)
            );
            
            $install->add_config_install($config_data);
            
            // Error log
            $schema_table = array(
                'COLUMNS' => array(
                    'log_id' => array('UINT', NULL, 'auto_increment'),
                    'time'   => array('TIMESTAMP', 0),
                    'errno'  => array('UINT', 1),
                    'errstr' => array('MTEXT', '')
                ),
                'PRIMARY_KEY' => 'log_id'
            );
    
            if (!$db_tools->sql_table_exists(MOD_TWITCH_INTERFACE_ERROR_LOG))
            {
                $install->create_table(MOD_TWITCH_INTERFACE_ERROR_LOG, $schema_table);
                
                add_log('admin', 'MOD_TWITCH_INTERFACE_LOG_TABLE_ADD', MOD_TWITCH_INTERFACE_ERROR_LOG);
            }
            
            // Output log
            $schema_table = array(
                'COLUMNS' => array(
                    'log_id' => array('UINT', NULL, 'auto_increment'),
                    'time'   => array('TIMESTAMP', 0),
                    'errstr' => array('MTEXT', '')
                ),
                'PRIMARY_KEY' => 'log_id'
            );
            
            if (!$db_tools->sql_table_exists(MOD_TWITCH_INTERFACE_OUTPUT_LOG))
            {
                $install->create_table(MOD_TWITCH_INTERFACE_OUTPUT_LOG, $schema_table);
                
                add_log('admin', 'MOD_TWITCH_INTERFACE_LOG_TABLE_ADD', MOD_TWITCH_INTERFACE_OUTPUT_LOG);
            }
            
            // Authorization code cache
            $schema_table = array(
                'COLUMNS'     => array(
                    'user_id' => array('UINT', 0),
                    'code'    => array('MTEXT', '')
                ),
                'PRIMARY_KEY' => 'user_id'
            );  
            
            if (!$db_tools->sql_table_exists(MOD_TWITCH_INTERFACE_CODE_CACHE))
            {
                $install->create_table(MOD_TWITCH_INTERFACE_CODE_CACHE, $schema_table);
                
                add_log('admin', 'MOD_TWITCH_INTERFACE_LOG_TABLE_ADD', MOD_TWITCH_INTERFACE_CODE_CACHE);
            }
            
            // Config data
            $schema_table = array(
                'COLUMNS' => array(
                    'config_name'   => array('MTEXT', ''),
                    'config_option' => array('MTEXT', '')
                ),
                'PRIMARY_KEY' => 'config_name'
            );  
            
            if (!$db_tools->sql_table_exists(MOD_TWITCH_INTERFACE_CONFIG))
            {
                $install->create_table(MOD_TWITCH_INTERFACE_CONFIG, $schema_table);
                
                add_log('admin', 'MOD_TWITCH_INTERFACE_LOG_TABLE_ADD', MOD_TWITCH_INTERFACE_CONFIG);
            }
            
            // Now insert the default config data
            $schema_table = array(
                'debug_level'            => 'FINE',
                'call_limit_setting'     => 'CALL_LIMIT_MAX',
                'key_name'               => 'name',
                'default_timeout'        => '5',
                'default_return_timeout' => '20',
                'api_version'            => '3',
                'token-send_method'      => 'HEADER',
                'retry_counter'          => '3',
                'client_id'              => '',
                'client_key'             => '',
                'client_uri'             => $phpbb_root_path . 'mod_twitch_interface/index.php'
            );
            
            if ($db_tools->sql_table_exists(MOD_TWITCH_INTERFACE_CONFIG))
            {
                $install->insert_table_data(MOD_TWITCH_INTERFACE_CONFIG, $schema_table);
                
                add_log('admin', 'MOD_TWITCH_INTERFACE_LOG_TABLE_DATA_INSERT', MOD_TWITCH_INTERFACE_CONFIG);
            }
        break;
        
        // We are deleting all of the structure in the DB
        case 'del':
            $install->delete_config('mod_twitch_interface_version');
            unset($config[$version]);
            
            // Deleter all of the added tables
            $tables = array(
               MOD_TWITCH_INTERFACE_ERROR_LOG,
               MOD_TWITCH_INTERFACE_OUTPUT_LOG,
               MOD_TWITCH_INTERFACE_CODE_CACHE,
               MOD_TWITCH_INTERFACE_CONFIG
            );
            
            $install->delete_table($tables);
            
            trigger_error(sprintf($user->lang['MOD_TWITCH_INTERFACE_INSTALL_UNINSTALLED'], TWITCH_INTERFACE_MOD_NAME) . '<br /><br />' . sprintf($user->lang['MOD_TWITCH_INTERFACE_INSTALL_BOARD_INDEX_REDIRECT'], $board_redirect));
        break;
        
        // Update the mod to a new version
        case 'update':
            // What version was reported in the DB
            switch(($config[$version]))
            {
                /**
                 * @todo - Fix output here
                 */ 
                case '1.0.0':
                    trigger_error(sprintf($user->lang['MOD_TWITCH_INTERFACE_INSTALL_CURRENT_VERSION_INSTALLED'], TWITCH_INTERFACE_MOD_NAME) . '<br /><br />' . sprintf($user->lang['MOD_TWITCH_INTERFACE_INSTALL_UNRECOGNIZED_VERSION_INSTALLED'], TWITCH_INTERFACE_MOD_NAME, $reinstal_mod));
                break;
                
                // Somehow the DB reports a version we don't recognize, toss an error to the admin attempting the update
                default:
                    trigger_error(sprintf($user->lang['MOD_TWITCH_INTERFACE_INSTALL_UNRECOGNIZED_VERSION_INSTALLED'], $config[$version]) . '<br /><br />' . sprintf($user->lang['MOD_TWITCH_INTERFACE_INSTALL_UNRECOGNIZED_VERSION_INSTALLED'], TWITCH_INTERFACE_MOD_NAME, $reinstal_mod));
                break;
            }
        break;
        
        // Install the mod and put in all of the stucture for it    
        case 'install':
    		$config_data = array(
                'mod_twitch_interface_version' => array(MOD_VERSION, 0)
            );
            
            $install->add_config_install($config_data);
            
            // Error log
            $schema_table = array(
                'COLUMNS' => array(
                    'log_id' => array('UINT', NULL, 'auto_increment'),
                    'time'   => array('TIMESTAMP', 0),
                    'errno'  => array('UINT', 1),
                    'errstr' => array('MTEXT', '')
                ),
                'PRIMARY_KEY' => 'log_id'
            );
    
            if (!$db_tools->sql_table_exists(MOD_TWITCH_INTERFACE_ERROR_LOG))
            {
                $install->create_table(MOD_TWITCH_INTERFACE_ERROR_LOG, $schema_table);
                
                add_log('admin', 'MOD_TWITCH_INTERFACE_LOG_TABLE_ADD', MOD_TWITCH_INTERFACE_ERROR_LOG);
            }
            
            // Output log
            $schema_table = array(
                'COLUMNS' => array(
                    'log_id' => array('UINT', NULL, 'auto_increment'),
                    'time'   => array('TIMESTAMP', 0),
                    'errstr' => array('MTEXT', '')
                ),
                'PRIMARY_KEY' => 'log_id'
            );
            
            if (!$db_tools->sql_table_exists(MOD_TWITCH_INTERFACE_OUTPUT_LOG))
            {
                $install->create_table(MOD_TWITCH_INTERFACE_OUTPUT_LOG, $schema_table);
                
                add_log('admin', 'MOD_TWITCH_INTERFACE_LOG_TABLE_ADD', MOD_TWITCH_INTERFACE_OUTPUT_LOG);
            }
            
            // Authorization code cache
            $schema_table = array(
                'COLUMNS'     => array(
                    'user_id' => array('UINT', 0),
                    'code'    => array('MTEXT', '')
                ),
                'PRIMARY_KEY' => 'user_id'
            );  
            
            if (!$db_tools->sql_table_exists(MOD_TWITCH_INTERFACE_CODE_CACHE))
            {
                $install->create_table(MOD_TWITCH_INTERFACE_CODE_CACHE, $schema_table);
                
                add_log('admin', 'MOD_TWITCH_INTERFACE_LOG_TABLE_ADD', MOD_TWITCH_INTERFACE_CODE_CACHE);
            }
            
            // Config data
            $schema_table = array(
                'COLUMNS' => array(
                    'config_name'   => array('MTEXT', ''),
                    'config_option' => array('MTEXT', '')
                ),
                'PRIMARY_KEY' => 'config_name'
            );  
            
            if (!$db_tools->sql_table_exists(MOD_TWITCH_INTERFACE_CONFIG))
            {
                $install->create_table(MOD_TWITCH_INTERFACE_CONFIG, $schema_table);
                
                add_log('admin', 'MOD_TWITCH_INTERFACE_LOG_TABLE_ADD', MOD_TWITCH_INTERFACE_CONFIG);
            }
            
            // Now insert the default config data
            $schema_table = array(
                'debug_level'            => 'FINE',
                'call_limit_setting'     => 'CALL_LIMIT_MAX',
                'key_name'               => 'name',
                'default_timeout'        => '5',
                'default_return_timeout' => '20',
                'api_version'            => '3',
                'token-send_method'      => 'HEADER',
                'retry_counter'          => '3',
                'client_id'              => '',
                'client_key'             => '',
                'client_uri'             => $phpbb_root_path . 'mod_twitch_interface/index.php'
            );
            
            if ($db_tools->sql_table_exists(MOD_TWITCH_INTERFACE_CONFIG))
            {
                $install->insert_table_data(MOD_TWITCH_INTERFACE_CONFIG, $schema_table);
                
                add_log('admin', 'MOD_TWITCH_INTERFACE_LOG_TABLE_DATA_INSERT', MOD_TWITCH_INTERFACE_CONFIG);
            }
        break;
        
        // Catch, redirect to the main install page
        default:
            trigger_error('MOD_TWITCH_INTERFACE_ERROR_NO_MODE');
        break;   
    }
}
?>