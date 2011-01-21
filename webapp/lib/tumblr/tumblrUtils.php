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

    static function save_tags_by_post_id($tumblr, $post_id) {
        $arr = tumblr_utils::get_json_map($tumblr->get_post_by_id($post_id, true));
        $post = $arr['posts'][0];
        
        $ts = $post['unix-timestamp'];
        $tags_map = array();
        foreach ($post['tags'] as $tag) {
            $tags_map[$tag] = array(array('id' => $post_id, 'ts' => $ts));
        }
        consolr_db::save_tags_list($tumblr->get_tumblr_name(),
                                   $tags_map,
                                   false,
                                   true);
    }

    static function get_thumbs_html($tumblr, $list, $thumbs_count, $images_per_row) {
        shuffle($list);

        $count = min($thumbs_count, count($list));
        $html = '<p>';
        for ($i = 0; $i < $count; $i++) {
            $l = $list[$i];
            $result = $tumblr->get_post_by_id($l['post_id'], true);
            $map = tumblr_utils::get_json_map($result);
            if (!empty($map)) {
                $post = $map['posts'][0];
    
                $html .= '<a href="' . $post['url'] . '">';
                $html .= '<img border="0" src="' . $post['photo-url-75'] . '"></img>';
                $html .= '</a>&nbsp;&nbsp;';
                if ((($i + 1) % $images_per_row) == 0) {
                    $html .= '</p><p>';
                }
            }
        }
        $html .= '</p>';

        return $html;
    }

    static function get_see_more_html($tumblr, $title, $tags, $thumbs_count, $images_per_row) {
        $list = consolr_db::get_posts_by_tags($tumblr->get_tumblr_name(), $tags);
        $content = '';
    
        if (count($list)) {
            $content = $title;
            $content .= tumblr_utils::get_thumbs_html($tumblr, $list, $thumbs_count, $images_per_row);
        }
    
        return $content;
    }
}
?>
