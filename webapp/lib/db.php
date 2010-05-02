<?php
class consolr_db {
    static function update_login_info($tumblr_name) {
        $get_uid_sql = "select uid from CONSOLR_LOGIN_INFO where tumblr_name='%tumblr_name%'";
        $update_login_sql = "update CONSOLR_LOGIN_INFO set last_login=now() where uid=%uid%";
        $insert_login_sql = "insert into CONSOLR_LOGIN_INFO (TUMBLR_NAME, LAST_LOGIN) values ('%tumblr_name%', now())";

        $db = consolr_db::connect();
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
}
?>