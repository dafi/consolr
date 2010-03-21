<?php

require_once 'tumblr.php';

class tumblr_utils {
    /**
     * Remove from json string the variable declaration and the semicolon at
     * the end. The result can be be used from PHP code
     * @param $tumblr_json the json string returned from tumblr API
     * @return associative array
     */
    static function get_json_map($tumblr_json) {
        preg_match('/{.*}/s', $tumblr_json, $matches);
        return json_decode($matches[0], true);
    }

    /**
     * Group posts by date, every post contains the new property unix-timestamp
     * @param $posts the associative array obtained from JSON format
     * @returns associative array in the form ['YYYYMMDD'] { posts }
     */
    static function group_posts_by_date($posts) {
        $grouped = array();

        foreach($posts as $post) {
            $post['unix-timestamp'] = strtotime($post['publish-on-time']);
            // ignore hours, minutes and seconds
            $str_time = strftime("%Y%m%d", $post['unix-timestamp']);

            if (array_key_exists($str_time, $grouped)) {
                $g = &$grouped[$str_time];
            } else {
                // destroy previous $g otherwise elements are added to last used array
                unset($g);
                $g = array();
                $grouped[$str_time] = &$g;
            }
            array_push($g, $post);
        }

        return $grouped;
    }
}
?>
