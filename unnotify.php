<?php
    // access tokens, username, etc. for Twitter
    require_once 'credentials.php';

    // Twitter library
    require_once 'twitteroauth-main/autoload.php';
    use Abraham\TwitterOAuth\TwitterOAuth;

    $twitter = new TwitterOauth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, TWITTER_ACCESS_TOKEN, TWITTER_ACCESS_TOKEN_SECRET);
    
    // first, get all of the friend objects, for their ids 
    $friends = $twitter->get('friends/ids');
    
    // set these counter variables to 0 explicitly, so we don't get warnings about undefined variables
    $notifications_off = $notifications_on = $total = 0; $notifiers = [];
    
    // Now, for each of those friends, use their ids to find their screen_names (usernames / handles):
    foreach ($friends->ids as $friend_id) {
        
        // get the $user object            
        $user = $twitter->get('users/show', array('user_id' => $friend_id));
        
        // and use its screen_name property to see if notifications are on or off for me
        $friendship = $twitter->get('friendships/show', array('source_screen_name' => MY_USERNAME, 'target_screen_name' => $user->screen_name));
        
        // tell me what the screen name is
        echo $total++ . ': ' . $user->screen_name . ': notifications ';
        
        // and whether notifications are ON or OFF
        if ($friendship->relationship->source->notifications_enabled) {
            echo 'ON'; $notifications_on++; // increment the ON counter for totals at the end
            $notifiers[] = $user->screen_name;
        }
        else {
            echo 'OFF'; $notifications_off++; // increment the OFF counter for totals at the end
        }
        echo "\n"; // line break after each user
        sleep(5);
    }
    // Then finally, tell me how many users I'm getting notified about versus how many not
    echo "---------------\n";
    echo "Total OFF: $notifications_off | Total ON: $notifications_on\n\n";

    // stat the count again, this time only of notifiers
    $total = 0;
    foreach ($notifiers as $screen_name) {
        
        $friendship = $twitter->post('friendships/update', array('screen_name' => $screen_name, 'device' => false));
        
        if (is_object($friendship)) { // I want to make sure I don't get those non-object errors again. let's make sure we got a result
            echo ++$total . " of $notifications_on: Notfications turned OFF for @$screen_name\n";
        }
        sleep(5);        
    }
?>