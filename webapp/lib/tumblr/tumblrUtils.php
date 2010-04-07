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
        if (count($matches)) {
            return json_decode($matches[0], true);
        }
        return array();
    }

    /**
     * Group posts by date
     * @param $posts the associative array obtained from JSON format
     * @package $date_property the property to use to get date, the default is
     * 'publish-on-time' used for queued posts
     * @returns associative array in the form ['YYYYMMDD'] { post_ids }
     */
    static function group_posts_by_date($posts, $date_property = 'publish-on-time') {
        $grouped = array();

        foreach($posts as $post) {
            // ignore hours, minutes and seconds
            $str_time = strftime("%Y%m%d", strtotime($post[$date_property]));

            if (array_key_exists($str_time, $grouped)) {
                $g = &$grouped[$str_time];
            } else {
                // destroy previous $g otherwise elements are added to last used array
                unset($g);
                $g = array();
                $grouped[$str_time] = &$g;
            }
            array_push($g, $post['id']);
        }

        return $grouped;
    }
}
?>
