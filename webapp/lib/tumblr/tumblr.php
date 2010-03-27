<?php
class tumblr {
    private $email;
    private $password;
    private $tumblr_name;

    function tumblr($email, $password, $tumblr_name) {
        $this->email = $email;
        $this->password = $password;
        $this->tumblr_name = $tumblr_name;
    }

    function get_queue($use_json = false, $start = '0', $num = '0', $type = '') {
        $api_url = 'http://' . $this->tumblr_name . '.tumblr.com/api/read';

        if ($use_json) {
            $api_url .= "/json";
        }
        $info = tumblr::do_request($api_url,
                    array(
                        'email'     => $this->email,
                        'password'  => $this->password,
                        'start'     => $start,
                        'num'       => $num,
                        'type'      => $type,
                        'state'     => 'queue'
                    ));

        return $info['result'];
    }

    function get_post_by_id($post_id, $use_json = false) {
        $api_url = 'http://' . $this->tumblr_name . '.tumblr.com/api/read';

        if ($use_json) {
            $api_url .= "/json";
        }
        $info = tumblr::do_request($api_url,
                    array(
                        'email'     => $this->email,
                        'password'  => $this->password,
                        'id'        => $post_id
                    ));

        return $info['result'];
    }

    function post_photo_to_queue($photo_url, $caption, $publish_time, $tags = null) {
        $api_url = 'http://www.tumblr.com/api/write';
        $info = tumblr::do_request($api_url,
                    array(
                        'email'     => $this->email,
                        'password'  => $this->password,

                        'type'      => 'photo',
                        'source'    => $photo_url,
                        'caption'   => $caption,

                        'group'     => $this->tumblr_name . '.tumblr.com',
                        'tags'      => $tags ? implode(',', $tags) : '',

                        'state'     => 'queue',
                        'publish-on' => $publish_time
                    ));
        return $info;
    }

    /**
     * @param merge_values read current post values and overwrite only the passed
     */
    function edit_post_queue_publish_date($post_id, $publish_time, $merge_values = false, $post_params = array()) {
        $api_url = 'http://www.tumblr.com/api/write';
        $params = array(
                        'email'     => $this->email,
                        'password'  => $this->password,

                        'post-id'   => $post_id,

                        'state'     => 'queue',
                        'publish-on' => $publish_time
                    );

        if ($merge_values) {
            // remove variable declaration and the semicolon at the end of string
            preg_match('/{.*}/s', $this->get_post_by_id($post_id, true), $matches);
            $json = json_decode($matches[0], true);
            $post = $json['posts'][0];
            $tags = isset($post['tags']) ? $post['tags'] : array();

            if ($post['type'] == 'photo') {
                $params['caption'] = isset($post_params['photo-caption'])
                                        ? $post_params['photo-caption']
                                        : $post['photo-caption'];
                if (isset($post_params['tags'])) {
                    $tags = array_unique(array_merge($tags, $post_params['tags']));
                }
            }
            $params['tags'] = implode(",", $tags);
        } else {
            $params['caption'] = isset($post_params['photo-caption'])
                                    ? $post_params['photo-caption']
                                    : '';
            $params['tags'] = isset($post_params['tags'])
                                ? implode(",", $post_params['tags'])
                                : "";
        }
        return tumblr::do_request($api_url, $params);
    }

    /**
     * @return an array containing 'status' (HTTP code) and result (the result)
     */
    public static function do_request($url, $params) {
        $request_data = http_build_query($params);

        // Send the POST request (with cURL)
        $c = curl_init($url);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $request_data);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($c);
        $status = curl_getinfo($c, CURLINFO_HTTP_CODE);
        curl_close($c);

        //if ($status == 201) {
        //    echo "Success! The new post ID is $result.\n";
        //} else if ($status == 403) {
        //    echo 'Bad email or password';
        //} else if ($status == 200) {
        //} else {
        //    echo "Tumblr Error ($status)\n$result\r";
        //}
        return array('status' => $status,
                     'result' => $result);
    }

    public function get_tumblr_name() {
        return $this->tumblr_name;
    }

    function get_published_posts($use_json = false, $start = 0, $num = 50) {
        $api_url = 'http://' . $this->tumblr_name . '.tumblr.com/api/read';

        if ($use_json) {
            $api_url .= "/json";
        }
        $info = tumblr::do_request($api_url,
                    array(
                        'start'     => $start,
                        'num'       => $num
                    ));

        return $info['result'];
    }
}

?>