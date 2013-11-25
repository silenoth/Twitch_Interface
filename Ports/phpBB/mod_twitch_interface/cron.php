<?php
require($phpbb_root_path . "includes/mod_twitch_interface/interface_compat.$phpEx");

class mod_twitch_interface_cron extends phpBBTwitch
{
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
                    
                    $users    = isset($unpack[1]) ? $unpack[1] : array();
                    $channels = isset($unpack[2]) ? $unpack[2] : array();
                    unset($unpack);
                    
                    $this->addFollows($users, $channels);
                break;
                
                // Another really expensive call to make in the que
                case 'delFollows':
                    // Unpack our parameters
                    $unpack = $params[$counter];
                    
                    $users    = isset($unpack[1]) ? $unpack[1] : array();
                    $channels = isset($unpack[2]) ? $unpack[2] : array();
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
?>