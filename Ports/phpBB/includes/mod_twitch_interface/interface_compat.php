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
    // Init the interface as a var here for OO calls
    var $twitchInterface = twitch;
    
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
        $token = $this->twitchInterface->generateToken($code);
        $check = $this->twitchInterface->checkToken($token);
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
        $result = $this->twitchInterface->getStreamsObjects(null, $channels, -1, 0, $embedable, $hls);
        $live = array();

        // Strip out the object data from the return
        foreach ($result as $key => $value)
        {
            $live[] = array($key);
        }

        // Cache the array of live channels
        $this->cacheLive($live);
    
        // We should not really need this, but the return will allow any calling function in the future to use the array
        return $live;
    }
    
    public static function cacheLive($data)
    {
        $row = '';
        
        // use write only mode here to delete any old cache data.  There will be a retry in the AJAX on this later if the file is being written
        if ($cacheHandle = @fopen(MOD_TWITCH_INTERFACE_CACHE_LIVE, 'w'))
        {
            // Lock the file while we are accessing it (useful if the cache is attempting to be written twice at some point)
            @flock($cacheHandle, LOCK_EX);    
            
            // Write the header
            fwrite($cacheHandle, '<?php exit; ?>' . "\n");
            // Time when the cache was constructed
            fwrite($cacheHandle, time() . "\n");
            // Now the number of live channels (Expected decoded returns)
            fwrite($cacheHandle, count($data) . "\n");
            
            // Finally, write the data itself
            foreach($data as $chan)
            {
                $row .= $chan . ':';
            }
            
            $row = rtrim($row, ':');
            fwrite($cacheHandle, $row);
            
            // Flush the file and unlock it
            @flock($cacheHandle, LOCK_UN);
            fclose($cacheHandle);
            
            // Make sure the file is read/write after everything is said and done
            phpbb_chmod(MOD_TWITCH_INTERFACE_CACHE_LIVE, CHMOD_READ | CHMOD_WRITE);
            
            return true;
        }
        
        return false;
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
            $this->postOutput('phpBBTwitch::addFollows', 'MOD_TWITCH_INTERFACE_LOG_PARAMATERS_ERROR');
            return false;
        }
        
        // Make the calls one at a time
        foreach($users as $key => $value)
        {
            // Grab the auth code so we can generate a token for the session
            $query = array();
            $sql = 'SELECT \'code\' FROM ' . MOD_TWITCH_INTERFACE_CODE_CACHE . ' WHERE username=' . $db->sql_escape($users[$key]);
            $authCode = $db->sql_query($sql);
            $db->sql_freeresult($result);
            
            if ($result)
            {
                $this->twitchInterface->followChan($users[$key], $channels[$key], null, $authCode);
            } else {
                $this->postOutput('phpBBTwitch::addFollows', 'User ' . $users[$key] . ' has not authorized to allow edits to follows');
            }
        }
        
        $this->postOutput('phpBBTwitch::addFollows', 'MOD_TWITCH_INTERFACE_LOG_FOLLOWS_SUCCESS');
    }
    
    /**
     * Takes a list of users and channels and attempts to remove the channel from that user's follows list
     * 
     * @param $users - [array] Array of all usernames in order of query
     * @param $channels - [array] Array of all channel names in order os query
     * 
     * @return true on completion.
     */ 
    public function delFollows($users, $channels)
    {
        global $db;        

        // Did something happen when we unpacked our params?
        if (!is_array($users) || !is_array($channels))
        {
            $this->postOutput('phpBBTwitch::delFollows', 'MOD_TWITCH_INTERFACE_LOG_PARAMATERS_ERROR');
            return false;
        }
        
        // Make the calls one at a time
        foreach($users as $key => $value)
        {
            // Grab the auth code so we can generate a token for the session
            $query = array();
            $sql = 'SELECT \'code\' FROM ' . MOD_TWITCH_INTERFACE_CODE_CACHE . ' WHERE username=' . $db->sql_escape($users[$key]);
            $authCode = $db->sql_query($sql);
            $db->sql_freeresult($result);
            
            if ($result)
            {
                $this->twitchInterface->unfollowChan($users[$key], $channels[$key], null, $authCode);
            } else {
                $this->postOutput('phpBBTwitch::delFollows', 'MOD_TWITCH_INTERFACE_LOG_NOT_AUTHORIZED');
            }
        }
        
        $this->postOutput('phpBBTwitch::delFollows', 'MOD_TWITCH_INTERFACE_LOG_UNFOLLOWS_SUCCESS');
        
        return true;
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
                    
                    $this->getLiveChannels($channels, $embedable, $hls);
                break;
                    
                // Likely the most expensive call to be made as this is done on a que.
                case 'addFollows':
                    // Unpack our parameters
                    $unpack = $params[$counter];
                    
                    $users    = isset($unpack[1]) ? $unpack[1] : null;
                    $channels = isset($unpack[2]) ? $unpack[2] : null;
                    unset($unpack);
                    
                    $this->addFollows($users, $channels);
                break;
                
                // Another really expensive call to make in the que
                case 'delFollows':
                    // Unpack our parameters
                    $unpack = $params[$counter];
                    
                    $users    = isset($unpack[1]) ? $unpack[1] : null;
                    $channels = isset($unpack[2]) ? $unpack[2] : null;
                    unset($unpack);
                    
                    $this->delFollows($users, $channels);
                break;
                
                // A catch case, break here for now
                default:
                break;                
            }
            
            $counter ++;
        }
    }
}
$data = array('testChannel1', 'testChannel2', 'testChannel3', 'testChannel4', 'testChannel5');

phpBBTwitch::cacheLive($data);

?>