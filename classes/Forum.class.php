<?php

class Forum {

    public function __construct(){
        
        global $phpbb_root_path, $phpEx, $user, $db, $config, $cache, $template, $auth;
        
        define('IN_PHPBB', true);
        $phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './forum/';
        $phpEx = substr(strrchr(__FILE__, '.'), 1);

        include($phpbb_root_path . 'common.' . $phpEx);
        include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
        include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

        // Start session management
        $user->session_begin();
        $auth->acl($user->data);

    }
    
    public function showPosts($type){

        if($type == 'announcements'){
            
            $query = 
            "SELECT u.user_game_id, u.user_id, u.username, t.topic_title, t.topic_poster, t.forum_id, t.topic_id, t.topic_time AS time, t.topic_replies, t.topic_first_post_id, p.poster_id, p.topic_id, p.post_id, p.post_text, p.bbcode_bitfield, p.bbcode_uid, p.post_time
            FROM phpbb_users u, phpbb_topics t, phpbb_posts p 
            WHERE u.user_id = t.topic_poster 
            AND u.user_id = p.poster_id 
            AND t.topic_id = p.topic_id 
            AND p.post_id = t.topic_first_post_id 
            AND t.forum_id = 26
            ORDER BY t.topic_time DESC";

            $limit = 4;

        } elseif($type == 'recent_posts') {

            $query = 
            "SELECT u.user_game_id, u.user_id, u.username, t.topic_title, t.forum_id, t.topic_id, t.topic_last_poster_name AS last, t.topic_last_post_time AS time, p.post_text
            FROM phpbb_users u, phpbb_topics t, phpbb_posts p
            WHERE u.user_id = t.topic_poster 
            AND u.user_id = p.poster_id 
            AND t.topic_id = p.topic_id 
            AND p.post_id = t.topic_first_post_id
            AND (t.topic_clan = 0 OR t.topic_clan = '".$_SESSION['CLAN_ID']."')
            ORDER BY t.topic_last_post_time DESC";

            $limit = 5;

        } else {
            
            $query = 'select 
                        phpbb_users.user_game_id, phpbb_posts.post_time as time, phpbb_posts.post_text as post_text, phpbb_posts.topic_id, phpbb_posts.forum_id, phpbb_topics.topic_title as topic_title, 
                        phpbb_users.username as username, phpbb_posts.post_username as anon 
                    from 
                        phpbb_posts, phpbb_topics, phpbb_users 
                    where 
                        post_approved=1 and 
                        phpbb_posts.topic_id=phpbb_topics.topic_id and 
                        phpbb_posts.poster_id=phpbb_users.user_id 
                        AND (phpbb_topics.topic_clan = 0 OR phpbb_topics.topic_clan = '.$_SESSION['CLAN_ID'].')
                        order by post_time desc';

            $limit = 7;                        
            
        }

        global $db;        

        $result = $db->sql_query_limit($query, $limit);
        
        $now = new DateTime('now');
        $player = new Player();

        ?>
            <ul class="recent-posts">
        <?php
        
        while($postInfo = $db->sql_fetchrow($result)){

            $postDate = new DateTime();
            $postDate->setTimestamp($postInfo['time']);
            $diffSeconds = round(abs($now->format('U') - $postDate->format('U')));
            
            $diff = $diffSeconds;
            $plural = '';
            $defined = false;
                        
            if($diff < 60){
                $time = (int)$diff;
                $magnitude = ' second';
                if($time > 1){
                    $plural = 's';
                }
                $defined = true;
            }
            $diff /= 60;
            if($diff < 60 && !$defined){
                $time = (int)$diff;
                $magnitude = ' minute';
                if($time > 1){
                    $plural = 's';
                }
                $defined = true;
            }
            $diff /= 24;
            if($diff < 24 && !$defined){
                $time = (int)$diff;
                $magnitude = ' hour';
                if($time > 1){
                    $plural = 's';
                }
                $defined = true;
            }
            $diff /= 24;
            if(!$defined){
                $time = (int)$diff;
                $magnitude = ' day';
                if($time > 1){
                    $plural = 's';
                }
            }
            
            $timeStr = $time.$magnitude.$plural;
            
            $timeStr .= ' ago';
            
            $postText = $postInfo['post_text'];
            
            $postTitle = $postInfo['topic_title'];
            if(strlen($postTitle) > 50){
                $postTitle = substr($postTitle, 0, 50);
                $postTitle .= '...';
            }
            
            require_once '/var/www/classes/Purifier.class.php';
            $purifier = new Purifier();
            $purifier->set_config('text');
                    
            if($type != 'recent_comments'){
            
                if(strlen($postText) > 100){
                    $postText = substr($postText, 0, 100);
                    $postText .= '...';
                }

                $textToShow = $postTitle.' - <span class="small nomargin">'.$purifier->purify($postText).'</span>';
                
            } else {
                
                if(strlen($postText) > 300){
                    $postText = substr($postText, 0, 300);
                    $postText .= '...';
                }
                
                $textToShow = '<span class="gray">'.$purifier->purify($postText).'</span>';
                
            }

            if($_SERVER['SERVER_NAME'] == 'localhost'){
                $postLink = '/forum/'.'viewtopic.php?f='.$postInfo['forum_id'].'&t='.$postInfo['topic_id'];
            } else {
                $postLink = 'https://forum.hackerexperience.com/'.'viewtopic.php?f='.$postInfo['forum_id'].'&t='.$postInfo['topic_id'];
            }
            
            switch($type){
                case 'announcements':
                    
                    $by = ' By: <a href="profile?id='.$postInfo['user_game_id'].'">'.$postInfo['username'].'</a> <span class="small">'.$timeStr.'</span>';
                    
                    break;
                case 'recent_posts':

                    $by = ' By: <a href="profile?id='.$postInfo['user_game_id'].'">'.$postInfo['username'].'</a> <span class="small">'.$timeStr.'</span>';
                    
                    if($postInfo['topic_replies'] == 0){
                        $replies = 'No replies';
                    } elseif($postInfo['topic_replies'] == 1){
                        $replies = '1 reply';
                    } else {
                        $replies = $postInfo['topic_replies'].' replies';
                    }
                    
                    $by .= ' ['.$replies.']';
                    
                    break;
                case 'recent_comments':
                    
                    $by = ' <a href="profile?id='.$postInfo['user_game_id'].'">'.$postInfo['username'].'</a> commented on post <span class="black">'.$postTitle.'</span> - <span class="small nomargin">'.$timeStr.'</span>';

                    break;
            }
                        
            $userImage = $player->getProfilePic($postInfo['user_game_id'], $postInfo['username'], TRUE);
            
?>
                
                <li>
                    <div class="user-thumb">
                        <img width="50" height="50" alt="User" src="<?php echo $userImage; ?>" />
                    </div>
                    <div class="article-post">
                        <span class="user-info"><?php echo $by; ?></span>
                        <p> 
                            <a href="<?php echo $postLink; ?>">
                                <?php echo $textToShow; ?>
                            </a>
                        </p>
                    </div>
                </li>

<?php
            
        }

        ?>
            </ul>
        <?php

    }
    
    public function externalRegister($username, $pass, $email, $gameID){
                
        global $phpbb_root_path, $phpEx, $user, $db, $config, $cache, $template, $auth;

        require $phpbb_root_path . 'includes/functions_user.php';
                 
        $user_row = Array (
            
                'username'				=> $username,
                'user_password'			=> phpbb_hash($pass),
                'user_email'			=> $email,
                'group_id'				=> 2,
                'user_timezone'			=> (float) 0,
                'user_dst'				=> 0,
                'user_lang'				=> 'en',
                'user_type'				=> 0,
                'user_actkey'			=> '',
                'user_ip'				=> '',
                'user_regdate'			=> time(),
                'user_inactive_reason'	=> 0,
                'user_new' => 1,
                'user_game_id' => $gameID,
            
        );
         
        $phpbb_user_id = user_add($user_row);
        
    }

    public function login($user, $pass, $special){

        global $auth;
        $auth->login($user, $pass, true, 1, 0, $special);
        
    }
    
    public function logout(){
        
        global $user;
        
        $user->session_kill();
        $user->session_begin();        
        
    }
    
    public function createForum($forum_name, $forum_clan){   
        
        $parent_id = 3;
        
        global $phpbb_root_path, $phpEx, $user, $auth, $cache, $db, $config, $template, $table_prefix;

        $response = array();
        $data = array(
           'forum_name' => $forum_name,
        );

        // Forum info
        $sql = 'SELECT forum_id
              FROM ' . FORUMS_TABLE . '
              WHERE ' . $db->sql_build_array('SELECT', $data);
        $result = $db->sql_query($sql);

        $forum_id = (int) $db->sql_fetchfield('forum_id');
        $db->sql_freeresult($result);

        if ($forum_id) 
        {
           $response['error'] = TRUE;
           $response['error_msg'] = 'FORUM_EXISTS';
           $response['forum_id'] = $forum_id;
        } 
        else 
        {
           $forum_data = array(
              'parent_id'   =>   $parent_id,
               //CUSTOM ON DB \/
              'forum_clan' => (int)$forum_clan,
               //CUSTOM /\
              'left_id'   =>   0,
              'right_id'   =>   0,
              'forum_parents'   =>   '',
              'forum_name'   =>   $data['forum_name'],
              'forum_desc'  =>   '',
              'forum_desc_bitfield'   =>   '',
              'forum_desc_options'   =>   7,
              'forum_desc_uid'   =>   '',
              'forum_link'   =>   '',
              'forum_password'   =>   '',
              'forum_style'   =>   0,
              'forum_image'   =>   '',
              'forum_rules'   =>   '',
              'forum_rules_link'   =>   '',
              'forum_rules_bitfield'   =>   '',
              'forum_rules_options'   =>   7,
              'forum_rules_uid'   =>   '',
              'forum_topics_per_page'   =>   0,
              'forum_type'   =>   1,
              'forum_status'   =>   0,
              'forum_posts'   =>   0,
              'forum_topics'   =>   0,
              'forum_topics_real'   =>   0,
              'forum_last_post_id'   =>   0,
              'forum_last_poster_id'   =>   0,
              'forum_last_post_subject'   =>   '',
              'forum_last_post_time'   =>   0,
              'forum_last_poster_name'   =>   '',
              'forum_last_poster_colour'   =>   '',
              'forum_flags'   =>   32,
              'display_on_index'   =>   FALSE,            
              'enable_indexing'   =>   TRUE,
              'enable_icons'   =>   FALSE,                
              'enable_prune'   =>   FALSE,
              'prune_next'   =>   0,
              'prune_days'   =>   7,                       
              'prune_viewed'   =>   7,                    
              'prune_freq'   =>   1,
           );
                /**
                /*Changed the code from here    
                /*Pulled straight from acl_forums.php from line 973 to line 1002        
                /*Removed lines 980 -> 989
                /*Changed $forum_data_sql['parent_id']; line 975 to $parent_id
                /*Changed $forum_data_sql on lines 1001, 1002 to $forum_data    
                **/

                $sql = 'SELECT left_id, right_id, forum_type
                    FROM ' . FORUMS_TABLE . '
                    WHERE forum_id = ' . $parent_id;
                $result = $db->sql_query($sql);
                $row = $db->sql_fetchrow($result);
                $db->sql_freeresult($result);

                $sql = 'UPDATE ' . FORUMS_TABLE . '
                    SET left_id = left_id + 2, right_id = right_id + 2
                    WHERE left_id > ' . $row['right_id'];
                $db->sql_query($sql);

                $sql = 'UPDATE ' . FORUMS_TABLE . '
                    SET right_id = right_id + 2
                    WHERE ' . $row['left_id'] . ' BETWEEN left_id AND right_id';
                $db->sql_query($sql);

                $forum_data['left_id'] = $row['right_id'];
                $forum_data['right_id'] = $row['right_id'] + 1;

           // And as last, a insert query
           $sql = 'INSERT INTO ' . FORUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $forum_data);
           $db->sql_query($sql);

           $forum_data['forum_id'] = $db->sql_nextid();
           $forumID = $forum_data['forum_id'];

           // successful result
           $response['error'] = FALSE;
           $response['error_msg'] = '';
           $response['forum_id'] = $forum_data['forum_id'];


           /* PERMISSIONS ----------------------------------------------- */

           // copy permissions from parent forum
           $forum_perm_from = $parent_id;

           ///////////////////////////
           // COPY USER PERMISSIONS //
           ///////////////////////////

           // Copy permisisons from/to the acl users table (only forum_id gets changed)
           $sql = 'SELECT user_id, auth_option_id, auth_role_id, auth_setting
              FROM ' . ACL_USERS_TABLE . '
              WHERE forum_id = ' . $forum_perm_from;
           $result = $db->sql_query($sql);

           $users_sql_ary = array();
           while ($row = $db->sql_fetchrow($result))
           {
              $users_sql_ary[] = array(
                 'user_id'         => (int) $row['user_id'],
                 'forum_id'         => $forum_data['forum_id'],
                 'auth_option_id'   => (int) $row['auth_option_id'],
                 'auth_role_id'      => (int) $row['auth_role_id'],
                 'auth_setting'      => (int) $row['auth_setting']
              );
           }
           $db->sql_freeresult($result);

           ////////////////////////////
           // COPY GROUP PERMISSIONS //
           ////////////////////////////

           // Copy permisisons from/to the acl groups table (only forum_id gets changed)
           $sql = 'SELECT group_id, auth_option_id, auth_role_id, auth_setting
              FROM ' . ACL_GROUPS_TABLE . '
              WHERE forum_id = ' . $forum_perm_from;
           $result = $db->sql_query($sql);

           $groups_sql_ary = array();
           while ($row = $db->sql_fetchrow($result))
           {
              $groups_sql_ary[] = array(
                 'group_id'         => (int) $row['group_id'],
                 'forum_id'         => $forum_data['forum_id'],
                 'auth_option_id'   => (int) $row['auth_option_id'],
                 'auth_role_id'      => (int) $row['auth_role_id'],
                 'auth_setting'      => (int) $row['auth_setting']
              );
           }
           $db->sql_freeresult($result);

           //////////////////////////////////
           // INSERT NEW FORUM PERMISSIONS //
           //////////////////////////////////

           $db->sql_multi_insert(ACL_USERS_TABLE, $users_sql_ary);
           $db->sql_multi_insert(ACL_GROUPS_TABLE, $groups_sql_ary);

           $auth->acl_clear_prefetch(); 
           
           //ADICIONANDO AS PERMISSOES DE USUÁRIO!

           $userID = $user->data['user_id'];
           
           $sql = "INSERT INTO phpbb_acl_users (user_id, forum_id, auth_role_id) VALUES ('".$userID."', '".$forumID."', '12')";           
           $db->sql_query($sql);
           
        }
    }
    
    public function getForumIDByGameID($gameID){
        
        global $db;

        $sql = 'SELECT user_id
                FROM phpbb_users
                WHERE user_game_id = ' . $gameID.' LIMIT 1';
        $result = $db->sql_query($sql);
        $data = $db->sql_fetchrow($result);

        return $data['user_id'];            
           
    }
    
    public function getForumClanID($clanID){
        
        global $db;

        $sql = 'SELECT forum_id, parent_id
                FROM phpbb_forums
                WHERE forum_clan = ' . $clanID.' LIMIT 1';
        $result = $db->sql_query($sql);
        $data = $db->sql_fetchrow($result);

        return $data;
        
    }
    
    public function setPermission($userID, $permissionType, $forumID){
        
        global $db, $user, $auth, $template, $cache;
        global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

        include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
        include_once($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
        include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

        $del = false;
        
        switch($permissionType){
            
            case 'viewer':
                // 2019: Set user as viewer of his clan forum
                //setar o usuário como viewer do forum do clan
                //essas configurações são do role 21 (standard access + polls)
                $settingArray = array ( $userID => array ( $forumID => array ( 'f_announce' => '-1', 'f_icons' => '1', 'f_list' => '1', 'f_post' => '1', 'f_read' => '1', 'f_reply' => '1', 'f_sticky' => '-1', 'f_attach' => '1', 'f_bbcode' => '1', 'f_download' => '1', 'f_flash' => '-1', 'f_img' => '1', 'f_sigs' => '1', 'f_smilies' => '1', 'f_bump' => '1', 'f_delete' => '1', 'f_edit' => '1', 'f_email' => '1', 'f_print' => '1', 'f_report' => '1', 'f_subscribe' => '1', 'f_user_lock' => '-1', 'f_ignoreflood' => '-1', 'f_noapprove' => '1', 'f_postcount' => '1', 'f_search' => '1', 'f_poll' => '1', 'f_vote' => '1', 'f_votechg' => '1', ), ), );
                $roleArray = array ( $userID => array ( $forumID => '21', ), );
                $type = 'f_';
                break;
            case 'mod':
                // 2019: Set user as moderator of his clan forum
                //setar o usuário como moderador do forum do clan dele (default para o admin do clan)
                //essas configurações são do role 12 (simple mod)
                $settingArray = array ( $userID => array ( $forumID => array ( 'm_approve' => '-1', 'm_chgposter' => '-1', 'm_delete' => '1', 'm_edit' => '1', 'm_report' => '1', 'm_info' => '1', 'm_lock' => '-1', 'm_merge' => '-1', 'm_move' => '-1', 'm_split' => '-1', ), ), );
                $roleArray = array ( $userID => array ( $forumID => '12', ), );
                $type = 'm_';
                break;
            case 'viewer_del':
                $del = true;
                $type = 'f_';
                break;
            case 'mod_del':
                $del = true;
                $type = 'm_';
                break;
            
        }
        
        $auth_admin = new auth_admin();
        
        if(!$del){
            $this->set_all_permissions($type, $auth_admin, $userID, '', $settingArray, $roleArray);
        } else {
            $this->acl_delete('user', $userID, $forumID, $type);
        }

        
    }
    
    public function acl_delete($mode, $ug_id = false, $forum_id = false, $permission_type = false)
    {
            global $db;

            if ($ug_id === false && $forum_id === false)
            {
                return;
            }

            $option_id_ary = array();
            $table = ($mode == 'user') ? ACL_USERS_TABLE : ACL_GROUPS_TABLE;
            $id_field = $mode . '_id';

            $where_sql = array();

            if ($forum_id !== false)
            {
                    $where_sql[] = (!is_array($forum_id)) ? 'forum_id = ' . (int) $forum_id : $db->sql_in_set('forum_id', array_map('intval', $forum_id));
            }

            if ($ug_id !== false)
            {
                    $where_sql[] = (!is_array($ug_id)) ? $id_field . ' = ' . (int) $ug_id : $db->sql_in_set($id_field, array_map('intval', $ug_id));
            }

            // There seem to be auth options involved, therefore we need to go through the list and make sure we capture roles correctly
            if ($permission_type !== false)
            {
                    // Get permission type
                    $sql = 'SELECT auth_option, auth_option_id
                            FROM ' . ACL_OPTIONS_TABLE . "
                            WHERE auth_option " . $db->sql_like_expression($permission_type . $db->any_char);
                    $result = $db->sql_query($sql);

                    $auth_id_ary = array();
                    while ($row = $db->sql_fetchrow($result))
                    {
                            $option_id_ary[] = $row['auth_option_id'];
                            $auth_id_ary[$row['auth_option']] = ACL_NO;
                    }
                    $db->sql_freeresult($result);

                    // First of all, lets grab the items having roles with the specified auth options assigned
                    $sql = "SELECT auth_role_id, $id_field, forum_id
                            FROM $table, " . ACL_ROLES_TABLE . " r
                            WHERE auth_role_id <> 0
                                    AND auth_role_id = r.role_id
                                    AND r.role_type = '{$permission_type}'
                                    AND " . implode(' AND ', $where_sql) . '
                            ORDER BY auth_role_id';
                    $result = $db->sql_query($sql);

                    $cur_role_auth = array();
                    while ($row = $db->sql_fetchrow($result))
                    {
                            $cur_role_auth[$row['auth_role_id']][$row['forum_id']][] = $row[$id_field];
                    }
                    $db->sql_freeresult($result);

                    // Get role data for resetting data
                    if (sizeof($cur_role_auth))
                    {
                            $sql = 'SELECT ao.auth_option, rd.role_id, rd.auth_setting
                                    FROM ' . ACL_OPTIONS_TABLE . ' ao, ' . ACL_ROLES_DATA_TABLE . ' rd
                                    WHERE ao.auth_option_id = rd.auth_option_id
                                            AND ' . $db->sql_in_set('rd.role_id', array_keys($cur_role_auth));
                            $result = $db->sql_query($sql);

                            $auth_settings = array();
                            while ($row = $db->sql_fetchrow($result))
                            {
                                    // We need to fill all auth_options, else setting it will fail...
                                    if (!isset($auth_settings[$row['role_id']]))
                                    {
                                            $auth_settings[$row['role_id']] = $auth_id_ary;
                                    }
                                    $auth_settings[$row['role_id']][$row['auth_option']] = $row['auth_setting'];
                            }
                            $db->sql_freeresult($result);

                            // Set the options
                            foreach ($cur_role_auth as $role_id => $auth_row)
                            {
                                    foreach ($auth_row as $f_id => $ug_row)
                                    {
                                            $this->acl_set($mode, $f_id, $ug_row, $auth_settings[$role_id], 0, false);
                                    }
                            }
                    }
            }

            // Now, normally remove permissions...
            if ($permission_type !== false)
            {
                    $where_sql[] = $db->sql_in_set('auth_option_id', array_map('intval', $option_id_ary));
            }

            $sql = "DELETE FROM $table
                    WHERE " . implode(' AND ', $where_sql);
            $db->sql_query($sql);

            $this->acl_clear_prefetch();
    }
    
	public function set_all_permissions($permission_type, &$auth_admin, &$user_id, $group_id, $settingArray, $roleArr)
	{
		global $user, $auth;

		// User or group to be set?
		$ug_type = (sizeof($user_id)) ? 'user' : 'group';
                
		$auth_settings = $settingArray;
		$auth_roles = (isset($_POST['role'])) ? $_POST['role'] : array();
		$ug_ids = $forum_ids = array();

		// We need to go through the auth settings
		foreach ($auth_settings as $ug_id => $forum_auth_row)
		{
			$ug_id = (int) $ug_id;
			$ug_ids[] = $ug_id;

			foreach ($forum_auth_row as $forum_id => $auth_options)
			{
				$forum_id = (int) $forum_id;
				$forum_ids[] = $forum_id;

				// Check role...
				$assigned_role = (isset($auth_roles[$ug_id][$forum_id])) ? (int) $auth_roles[$ug_id][$forum_id] : 0;

				// If the auth settings differ from the assigned role, then do not set a role...
				if ($assigned_role)
				{
					if (!$this->check_assigned_role($assigned_role, $auth_options))
					{
						$assigned_role = 0;
					}
				}
                                
				// Update the permission set...
				$auth_admin->acl_set($ug_type, $forum_id, $ug_id, $auth_options, $assigned_role, false);
			}
		}
                
		$auth_admin->acl_clear_prefetch();

		// Do we need to recache the moderator lists?
		if ($permission_type == 'm_')
		{
			cache_moderators();
		}

		// Remove users who are now moderators or admins from everyones foes list
		if ($permission_type == 'm_' || $permission_type == 'a_')
		{
			update_foes($group_id, $user_id);
		}

	}
        
    public function acl_clear_prefetch($user_id = false)
	{
		global $db, $cache;

		// Rebuild options cache
		$cache->destroy('_role_cache');

		$sql = 'SELECT *
			FROM ' . ACL_ROLES_DATA_TABLE . '
			ORDER BY role_id ASC';
		$result = $db->sql_query($sql);

		$this->role_cache = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$this->role_cache[$row['role_id']][$row['auth_option_id']] = (int) $row['auth_setting'];
		}
		$db->sql_freeresult($result);

		foreach ($this->role_cache as $role_id => $role_options)
		{
			$this->role_cache[$role_id] = serialize($role_options);
		}

		$cache->put('_role_cache', $this->role_cache);

		// Now empty user permissions
		$where_sql = '';

		if ($user_id !== false)
		{
			$user_id = (!is_array($user_id)) ? $user_id = array((int) $user_id) : array_map('intval', $user_id);
			$where_sql = ' WHERE ' . $db->sql_in_set('user_id', $user_id);
		}

		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_permissions = '',
				user_perm_from = 0
			$where_sql";
		$db->sql_query($sql);

		return;
	}
    
}

?>
