<?php
/**
 * This is the compatability file for the phpBB port of Anthony 'IBurn36360' Diaz's
 * Twitch_Interface.  This file and all other files are provided and are protected 
 * by the following license:
 * 
 * This Twitch Interface is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This Twitch Interface is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * A copy of the GNU GPLV3 license can be found at http://www.gnu.org/licenses/
 * or can be found in the folder distributed with the software.
 */ 
 
class phpBBTwitch // Provides all functions to interact with the interface from phpBB's end
{
    public function postError($errNo, $errStr)
    {
        global $db;
        
        $collumns = array(
            'time' => time(),
            'errno' => $db->sql_escape($errNo),
            'errstr' => $db->sql_escape($errStr)
            );
        
        $sql = 'INSERT INTO ' . MOD_TWITCH_INTERFACE_ERROR_LOG . ' ' . $db->sql_build_query('INSERT', $collumns);
        $db->sql_query($sql);
    }
    
    public function addAuthorizationCode($code)
    {
        global $db, $user;
        
        // A quick way of grabbing the display name from twitch
        $token = twitch::generateToken($code);
        $check = twitch::checkToken($token);
        $username = $check['name'];
        
        $collumns = array(
            'id' => $user->data['user_id'],
            'code' => $db->sql_escape($code),
            'username' => $db->sql_escape($username)
            );
        
        $sql = 'INSERT INTO ' . MOD_TWITCH_INTERFACE_CODE_CACHE . ' ' . $db->sql_build_query('INSERT', $collumns);
        $db->sql_query($sql);
    }
    
    public function postOutput($function, $errStr)
    {
        global $db;
        
        $collumns = array(
            'time' => time(),
            'errstr' => $db->sql_escape($errStr)
            );
        
        $sql = 'INSERT INTO ' . MOD_TWITCH_INTERFACE_OUTPUT_LOG . ' ' . $db->sql_build_query('INSERT', $collumns);
        $db->sql_query($sql);        
    }
    
    public function getLiveChannels($channels = array(), $embedable = false, $hls = false)
    {
        $live = twitch::getStreamsObjects(null, $channels, -1, 0, $embedable, $hls);

        return $live;
    }
    
    public function purgeOutput()
    {
        $sql = 'DELETE * FROM ' . MOD_TWITCH_INTERFACE_OUTPUT_LOG . ';';
        $db->sql_query($sql);
        
        add_log('admin', 'LOG_MOD_TWITCH_OUTPUT_CLEARED', 'output');
    }
    
    public function purgeErrors()
    {
        $sql = 'DELETE * FROM ' . MOD_TWITCH_INTERFACE_ERROR_LOG . ';';
        $db->sql_query($sql);
        
        add_log('admin', 'LOG_MOD_TWITCH_OUTPUT_CLEARED', 'error');
    }
    
    public function purgeAuthorizationCodes()
    {
        $sql = 'DELETE * FROM ' . MOD_TWITCH_INTERFACE_CODE_CACHE . ';';
        $db->sql_query($sql);
        
        add_log('admin', 'MOD_TWITCH_INTERFACE_CODE_CACHE_CLEARED', 'error');        
    }
    
    // Expensive function, will sort through the array of params and add channel to user's follows list
    public function addFollows($users, $channels)
    {
        global $db;
        
        // Did something happen when we unpacked our params?
        if (!is_array($users) || !is_array($channels) || (count($users) !== count($channels)))
        {
            self::postOutput('phpBBTwitch::addFollows', 'MOD_TWITCH_INTERFACE_LOG_PARAMATERS_ERROR');
            return false;
        }
        
        // Make the calls one at a time
        foreach($users as $key => $value)
        {
            // Grab the auth code so we can generate a token for the session
            $query = array();
            $sql = 'SELECT \'code\' FROM ' . MOD_TWITCH_INTERFACE_CODE_CACHE . ' WHERE username=' . $users[$key];
            $authCode = $db->sql_query($sql);
            $db->sql_freeresult($result);
            
            twitch::followChan($users[$key], $channels[$key], null, $authCode);
        }
        
        self::postOutput('phpBBTwitch::addFollows', 'MOD_TWITCH_INTERFACE_LOG_FOLLOWS_SUCCESS');
    }
    
    // Expensive function, will sort through the array of params and remove channel from user's follows list
    public function delFollows($users, $channels)
    {
        global $db;        

        // Did something happen when we unpacked our params?
        if (!is_array($users) || !is_array($channels))
        {
            self::postOutput('phpBBTwitch::addFollows', 'MOD_TWITCH_INTERFACE_LOG_PARAMATERS_ERROR');
            return false;
        }
        
        // Make the calls one at a time
        foreach($users as $key => $value)
        {
            // Grab the auth code so we can generate a token for the session
            $query = array();
            $sql = 'SELECT \'code\' FROM ' . MOD_TWITCH_INTERFACE_CODE_CACHE . ' WHERE username=' . $users[$key];
            $authCode = $db->sql_query($sql);
            $db->sql_freeresult($result);
            
            twitch::unfollowChan($users[$key], $channels[$key], null, $authCode);
        }
        
        self::postOutput('phpBBTwitch::addFollows', 'MOD_TWITCH_INTERFACE_LOG_UNFOLLOWS_SUCCESS');
        
    }
        
    // Our cron task handler
    public function cron($cronTasks = array(), $params = array())
    {
        // Keep track of where we are in the array set
        $counter = 0;
        
        // Switch through our que of tasks to do and apply the target params to the case
        foreach ($cronTasks as $task)
        {
            switch($task)
            {
                // The only task that will be performed on a timer.
                case 'getLive':
                    // Unpack our parameters
                    $unpack = $params[$counter];
                    
                    $channels  = $unpack[0];  // This is required, so no need to check for the existance
                    $embedable = isset($unpack[1]) ? $unpack[1] : false;
                    $hls       = isset($unpack[2]) ? $unpack[2] : false;
                    unset($unpack); // We are done with this now
                    
                    self::getLiveChannels($channels, $embedable, $hls);
                break;
                
                // Added to the que on request
                case 'purgeOutput':
                    self::purgeOutput();
                break;
                    
                // Added to the que on request
                case 'purgeErrors':
                    self::purgeErrors();
                break;
                
                // Added to the que on request
                case 'purgeAuthorizationCodes':
                    self::purgeAuthorizationCodes();
                break;
                    
                // Likely the most expensive call to be made as this is done on a que.
                case 'addFollows':
                    // Unpack our parameters
                    $unpack = $params[$counter];
                    
                    $users    = isset($unpack[1]) ? $unpack[1] : null;
                    $channels = isset($unpack[2]) ? $unpack[2] : null;
                    unset($unpack);
                    
                    self::addFollows($users, $channels);
                break;
                
                // Another really expensive call to make in the que
                case 'delFollows':
                    // Unpack our parameters
                    $unpack = $params[$counter];
                    
                    $users    = isset($unpack[1]) ? $unpack[1] : null;
                    $channels = isset($unpack[2]) ? $unpack[2] : null;
                    unset($unpack);
                    
                    self::delFollows($users, $channels);
                break;
                
                // A catch case, break here for now
                default:
                break;                
            }
            
            $counter ++;
        }
    }
}
?>