<?php
if (!defined('IN_PHPBB') || !defined('IN_INSTALL'))
{
    exit;
}

class twitchInterfaceInstaller
{
	function add_config_install($config_data)
	{
		global $config, $db;
		
		foreach ($config_data as $config_name => $config_array)
		{
			set_config($config_name, $config_array[0], $config_array[1]);
		}
		return true;
	}
    
 	function delete_config($config_name)
	{
		global $config, $db;
		
		if (isset($config[$config_name]))
		{
			$sql = 'DELETE FROM ' . CONFIG_TABLE . ' WHERE config_name = \'' . $db->sql_escape($config_name) . '\'';
			$db->sql_query($sql);
			
			unset($config[$config_name]);
			return true;
		}
		return false;
	}
    
	function delete_table($tables)
	{
		global $db, $db_tools;
		
		foreach ($tables as $table_name)
		{
			if ($db_tools->sql_table_exists($table_name))
			{
				$db_tools->sql_table_drop($table_name);
			}
		}
	}
    
    function create_table($table, $collumns = array())
    {
        global $db, $db_tools;
        
        if (!$db_tools->sql_table_exists($table))
        {
            $db_tools->sql_create_table($table, $collumns);
        }
        
    }
    
    function insert_table_data($table, $collumns = array())
    {
        global $db, $db_tools;
        
        if ($db_tools->sql_table_exists($table))
        {
            $sql = 'INSERT INTO ' . $table . ' ' . $db->sql_build_query('INSERT', $collumns);
            $db->sql_query($sql);
        }
    }
    
	function module_exists($class, $module)
	{
		global $db;
		
		if (!$module || !$class)
		{
			return true;
		}
		
        $class = $db->sql_escape($class);
		$module = $db->sql_escape($module);
		
        $sql = 'SELECT module_id FROM ' . MODULES_TABLE . 'WHERE module_langname = \'$module\' AND module_class = \'$class\'';
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
        if ($row)
		{
			return true;
		}
		return false;
	}
	
	function delete_module($module_name, $class)
	{
		global $db, $cache, $user;
		
		$class = $db->sql_escape($class);
		$module_name = $db->sql_escape($module_name);
		
        $sql = 'SELECT module_langname, module_id FROM ' . MODULES_TABLE . 'WHERE module_langname LIKE \'' . $module_name . '%\' AND module_class = \'$class\'';
		$result = $db->sql_query($sql);
		
        if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				add_log('admin', 'LOG_INSTALL_MODULE_DEL', $class, $this->lang_install($row['module_langname']));
				$sql = 'DELETE FROM ' . MODULES_TABLE . ' WHERE module_id = ' .$row['module_id'];
				$db->sql_query($sql);
			}
			while ($row = $db->sql_fetchrow($result));
		}
		
        $db->sql_freeresult($result);
		$cache->destroy("_modules_$class");
	}
}
?>