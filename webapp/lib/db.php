<?php

class consolr_db {
    static function update_login_info($tumblr_name) {
        $db = consolr_db::connect();

        $tumblr_name = mysql_real_escape_string($tumblr_name);
        $get_uid_sql = "select uid from CONSOLR_LOGIN_INFO where tumblr_name='%tumblr_name%'";
        $update_login_sql = "update CONSOLR_LOGIN_INFO set last_login=now() where uid=%uid%";
        $insert_login_sql = "insert into CONSOLR_LOGIN_INFO (TUMBLR_NAME, LAST_LOGIN) values ('%tumblr_name%', now())";

        $query = str_replace('%tumblr_name%', $tumblr_name, $get_uid_sql);
        $result = mysql_query($query, $db);

        //if (!$result) {
        //    die('Invalid query: ' . mysql_error());
        //}

        if (mysql_num_rows($result) == 1) {
            $uid = mysql_result($result, 0);
            $query = str_replace('%uid%', $uid, $update_login_sql);
            $result = mysql_query($query, $db);
        } else {
            $query = str_replace('%tumblr_name%', $tumblr_name, $insert_login_sql);
            $result = mysql_query($query, $db);
        }

        //if (!$result) {
        //    die('Invalid query: ' . mysql_error());
        //}

        mysql_close($db);
    }

    static function connect() {
        global $db_host, $db_user, $db_password, $db_name;

        $db = mysql_connect($db_host, $db_user, $db_password);
        if ($db == FALSE) {
            die ("Unable to connect to database");
        }
        if (strlen($db_name)) {
            mysql_select_db($db_name, $db) or die ("Error while setting database");
        }

        return $db;
    }

    static function save_tags_list($tumblr_name, $tags_list, $delete_tags, $continue_if_exists = false) {
        $db = consolr_db::connect();

        $tumblr_name = mysql_real_escape_string($tumblr_name);
        $delete_tags_sql = "delete from CONSOLR_POST_TAG where tumblr_name='%tumblr_name%'";
        $insert_tags_sql = "insert %ignore% into CONSOLR_POST_TAG (TUMBLR_NAME, POST_ID, TAG, PUBLISH_TIMESTAMP) values ('%tumblr_name%', %post_id%, '%tag%', %ts%)";
        $insert_tags_sql = str_replace('%ignore%', $continue_if_exists ? 'IGNORE' : '', $insert_tags_sql);

        if ($delete_tags) {
            $query = str_replace('%tumblr_name%', $tumblr_name, $delete_tags_sql);
            $result = mysql_query($query, $db);
            if (!$result) {
                die('Error while deleting tags: ' . mysql_error());
            }
        }

        foreach ($tags_list as $tags => $posts) {
            foreach ($posts as $post) {
                $query = str_replace('%tumblr_name%', $tumblr_name, $insert_tags_sql);
                $query = str_replace('%post_id%', $post['id'], $query);
                $query = str_replace('%tag%', mysql_real_escape_string($tags), $query);
                $query = str_replace('%ts%', $post['ts'], $query);

                $result = mysql_query($query, $db);

                if (!$result) {
                    die('Error while saving tags: ' . mysql_error());
                }
            }
        }
        mysql_close($db);
    }

    static function get_tags($tumblr_name) {
        $db = consolr_db::connect();

        $tumblr_name = mysql_real_escape_string($tumblr_name);
        $select_tags_sql = "SELECT tag, count(post_id) as post_count"
                            . " FROM CONSOLR_POST_TAG"
                            . " WHERE tumblr_name='%tumblr_name%'"
                            . " GROUP BY tag"
                            . " ORDER BY tag";

        $query = str_replace('%tumblr_name%', $tumblr_name, $select_tags_sql);
        $result = mysql_query($query, $db);

        if (!$result) {
            die('Invalid query: ' . mysql_error());
        }

        $tags = array();
        while ($row = mysql_fetch_assoc($result)) {
            array_push($tags, array('tag' => utf8_encode($row['tag']),
                                    'post_count' => $row['post_count']));
        }

        mysql_free_result($result);

        mysql_close($db);

        return $tags;
    }

    static function get_posts_count($tumblr_name) {
        $db = consolr_db::connect();

        $tumblr_name = mysql_real_escape_string($tumblr_name);
        $select_tags_sql = "SELECT count(distinct(post_id)) as posts_count FROM CONSOLR_POST_TAG where tumblr_name='%tumblr_name%'";

        $query = str_replace('%tumblr_name%', $tumblr_name, $select_tags_sql);
        $result = mysql_query($query, $db);

        if (!$result) {
            die('Invalid query: ' . mysql_error());
        }

        $row = mysql_fetch_assoc($result);
        $count = $row['posts_count'];

        mysql_free_result($result);

        mysql_close($db);
        return $count;
    }

    /**
     * Set a setting key having a single value
     * @param $userid the userid
     * @param $setting_key the key to retrieve
     * @return null if the key doesn't exists, false if multiple values
     * exist for the passed key, the value otherwise
     */
    static function get_single_setting($userid, $setting_key) {
        $db = consolr_db::connect();

        $userid = mysql_real_escape_string($userid);
        $setting_key = mysql_real_escape_string($setting_key);
        $select_sql = "SELECT SETTING_VALUE FROM CONSOLR_SETTINGS"
            . " where UID='%userid%' and SETTING_KEY='%setting_key%'";

        $query = str_replace('%userid%', $userid, $select_sql);
        $query = str_replace('%setting_key%', $setting_key, $query);
        $result = mysql_query($query, $db);

        if (!$result) {
            die('Invalid query: ' . mysql_error());
        }

        $count = mysql_num_rows($result);

        if ($count == 0) {
            $value = null;
        } else if ($count > 1) {
            echo 'Error: Returned more than one result';
            $value = false;
        } else {
            $row = mysql_fetch_assoc($result);
            $value = $row['SETTING_VALUE'];
        }

        mysql_free_result($result);

        mysql_close($db);
        return $value;
    }

    static function set_single_setting($userid, $setting_key, $setting_value) {
        $db = consolr_db::connect();

        $userid = mysql_real_escape_string($userid);
        $setting_key = mysql_real_escape_string($setting_key);
        $setting_value = mysql_real_escape_string($setting_value);
        $update_sql = "update CONSOLR_SETTINGS set SETTING_VALUE='%setting_value%'"
            . " where UID='%userid%' and SETTING_KEY='%setting_key%'";
        $insert_sql = "insert into CONSOLR_SETTINGS (UID, SETTING_KEY, SETTING_VALUE)"
            . " values ('%userid%', '%setting_key%', '%setting_value%')";

        $query = str_replace('%userid%', $userid, $update_sql);
        $query = str_replace('%setting_key%', $setting_key, $query);
        $query = str_replace('%setting_value%', $setting_value, $query);
        $result = mysql_query($query, $db);
        $ret_val = true;

        if (!$result) {
            echo 'Error while updating setting: ' . mysql_error();
            $ret_val = false;
        } else {
            $count = mysql_affected_rows($db);

            if ($count == 0) {
                $query = str_replace('%userid%', $userid, $insert_sql);
                $query = str_replace('%setting_key%', $setting_key, $query);
                $query = str_replace('%setting_value%', $setting_value, $query);
                $result = mysql_query($query, $db);

                if (!$result) {
                    echo 'Error while inserting setting: ' . mysql_error();
                    $ret_val = false;
                }
            }
        }
        mysql_close($db);

        return $ret_val;
    }

    static function get_posts_by_publish_range($tumblr_name, $ts1, $ts2) {
        $db = consolr_db::connect();

        $tumblr_name = mysql_real_escape_string($tumblr_name);
        $select_tags_sql = "SELECT distinct post_id, publish_timestamp"
                            . " FROM CONSOLR_POST_TAG"
                            . " WHERE tumblr_name='%tumblr_name%'"
                            . " and publish_timestamp between %ts1% and %ts2%"
                            . " ORDER BY publish_timestamp";


        $query = str_replace('%tumblr_name%', $tumblr_name, $select_tags_sql);
        $query = str_replace('%ts1%', $ts1, $query);
        $query = str_replace('%ts2%', $ts2, $query);
        $result = mysql_query($query, $db);

        if (!$result) {
            die('Invalid query: ' . mysql_error());
        }

        $posts = array();
        while ($row = mysql_fetch_assoc($result)) {
            array_push($posts, array('post_id' => $row['post_id'],
                                    'publish_timestamp' => $row['publish_timestamp']));
        }

        mysql_free_result($result);

        mysql_close($db);

        return $posts;
    }

    static function delete_tags_by_post_id($tumblr_name, $post_id) {
        $db = consolr_db::connect();

        $tumblr_name = mysql_real_escape_string($tumblr_name);
        $delete_tags_sql = "delete from CONSOLR_POST_TAG where tumblr_name='%tumblr_name%' AND post_id=%post_id%";

        $query = str_replace('%tumblr_name%', $tumblr_name, $delete_tags_sql);
        $query = str_replace('%post_id%', $post_id, $query);
        $result = mysql_query($query, $db);
        if (!$result) {
            die('Error while deleting tags: ' . mysql_error());
        }

        mysql_close($db);
    }

    /**
     * @returns an array containing post_id, publish_timestamp, tag
     * The array is sorted by publish_timestamp
     */
    static function get_posts_by_tags($tumblr_name, $tags_array) {
        $db = consolr_db::connect();

        $tumblr_name = mysql_real_escape_string($tumblr_name);
        $select_tags_sql = "SELECT distinct post_id, publish_timestamp, tag"
                            . " FROM CONSOLR_POST_TAG"
                            . " WHERE tumblr_name='%tumblr_name%'"
                            . " and (%tags%)"
                            . " ORDER BY publish_timestamp";

        $posts = array();

        if (count($tags_array)) {
            $where_clause = '';
            $sqlOR = " OR ";
            foreach ($tags_array as $tag) {
                $where_clause .= "tag = '" . mysql_real_escape_string($tag) . "'" . $sqlOR;
            }
            $where_clause = substr($where_clause, 0, strlen($where_clause) - strlen($sqlOR));

            $query = str_replace('%tumblr_name%', $tumblr_name, $select_tags_sql);
            $query = str_replace('%tags%', $where_clause, $query);
            $result = mysql_query($query, $db);

            if (!$result) {
                die('Invalid query: ' . mysql_error());
            }

            while ($row = mysql_fetch_assoc($result)) {
                array_push($posts, array('post_id' => $row['post_id'],
                                        'publish_timestamp' => $row['publish_timestamp'],
                                        'tag' => $row['tag']));
            }

            mysql_free_result($result);
            mysql_close($db);
        }

        return $posts;
    }

    static function get_last_published_posts_by_tag($tumblr_name, $tags_array) {
        $db = consolr_db::connect();

        $tumblr_name = mysql_real_escape_string($tumblr_name);
        $select_tags_sql = "(SELECT post_id, publish_timestamp, tag"
                            . " FROM CONSOLR_POST_TAG t"
                            . " WHERE tumblr_name='%tumblr_name%'"
                            . " AND t.tag='%tag_name%'"
                            . " ORDER BY publish_timestamp desc limit 1)";

        $posts = array();

        if (count($tags_array)) {
            $queries = array();
            foreach ($tags_array as $tag) {
                $query = str_replace('%tumblr_name%', $tumblr_name, $select_tags_sql);
                $query = str_replace('%tag_name%', mysql_real_escape_string($tag), $query);
                array_push($queries, $query);
            }

            $union_query = implode(" UNION ", $queries);
            $result = mysql_query($union_query, $db);

            if (!$result) {
                die('Invalid query: ' . mysql_error());
            }

            while ($row = mysql_fetch_assoc($result)) {
                array_push($posts, array('post_id' => $row['post_id'],
                                        'publish_timestamp' => $row['publish_timestamp'],
                                        'tag' => $row['tag']));
            }

            mysql_free_result($result);
            mysql_close($db);
        }

        return $posts;
    }
}
?>