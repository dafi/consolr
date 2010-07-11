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

    static function save_tags_list($tumblr_name, $tags_list, $delete_tags) {
        $db = consolr_db::connect();

        $tumblr_name = mysql_real_escape_string($tumblr_name);
        $delete_tags_sql = "delete from CONSOLR_POST_TAG where tumblr_name='%tumblr_name%'";
        $insert_tags_sql = "insert into CONSOLR_POST_TAG (TUMBLR_NAME, POST_ID, TAG) values ('%tumblr_name%', %post_id%, '%tag%')";

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
                $query = str_replace('%post_id%', $post, $query);
                $query = str_replace('%tag%', mysql_real_escape_string($tags), $query);

                $result = mysql_query($query, $db);

                if (!$result) {
                    die('Error while saving post: ' . mysql_error());
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
            array_push($tags, array('tag' => $row['tag'],
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
}
?>