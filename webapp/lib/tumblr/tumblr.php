<?php
abstract class abstract_tumblr {
    protected $tumblr_name;
    private $user_id;

    /**
     * Must be overwritten by subclasses
     * @return array containing 'status' and 'result'
     * status containg the HTTP response status
     * result contains the response content
     */
    protected abstract function do_logged_request($url, $params);

    protected function get_api_url($suffix) {
        return 'http://api.tumblr.com/v2/blog/' . $this->tumblr_name . '.tumblr.com' . $suffix;
    }

    public function get_userid() {
        if (!isset($user_id)) {
            $list = $this->get_tumblr_list();
            foreach ($list as $l) {
                if ($l['primary'] == '1') {
                    $this->user_id = $l['name'];
                    break;
                }
            }
        }
        return $this->user_id;
    }

    function get_queue() {
        // TODO Error 500
        return $this->do_logged_request($this->get_api_url('/posts/queue'), array());
    }

    function get_draft() {
        return $this->do_logged_request($this->get_api_url('/posts/draft'), array());
    }

    function get_post_by_id($post_id) {
        $info = $this->do_logged_request($this->get_api_url('/posts'),
                    array(
                        'id'        => $post_id,
                        'base-hostname' => $this->tumblr_name . '.tumblr.com',
                        'api_key' => OAUTH_CONSUMER_KEY
                    ));

        return $info['result'];
    }

    function get_draft_post_by_id($post_id) {
        // API v2 doesn't retrive post by draft but only from published so we use API v1
        $api_url = 'http://' . $this->tumblr_name . '.tumblr.com/api/read/json';
    
        $info = $this->do_logged_request($api_url,
                    array(
                        'id'        => $post_id
                    ));
    
        return $info['result'];
    }

    function post_photo_to_queue($photo_url, $caption, $publish_time, $tags = null) {
        $params = array(
                        'state'     => 'queue',
                        'type'      => 'photo',
                        'source'    => $photo_url,
                        'caption'   => $caption,
                        'tags'      => $tags ? implode(',', $tags) : '',
                        'publish-on' => $publish_time
                       );

        return $this->create_post($params);
    }

    function update_photo_post($post_id, $state, $merge_values = false, $post_params = array()) {
        $params = array(
                        'id'   => $post_id,
                        'base-hostname' => $this->tumblr_name . '.tumblr.com',
                        'api_key' => OAUTH_CONSUMER_KEY
                    );

        $params['caption'] = isset($post_params['photo-caption'])
                                ? $post_params['photo-caption']
                                : '';
        $params['tags'] = isset($post_params['tags'])
                            ? implode(",", $post_params['tags'])
                            : "";
        $params['link'] = isset($post_params['click-through-url'])
                                ? $post_params['click-through-url']
                                : '';
        return $this->do_logged_request($this->get_api_url('/post/edit'), $params);
    }

    /**
     * @param merge_values read current post values and overwrite only the passed
     */
    function edit_post_queue_publish_date($post_id, $publish_time, $merge_values = false, $post_params = array()) {
        $api_url = 'http://www.tumblr.com/api/write';
        $params = array(
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
            $params['photo-link-url'] = isset($post_params['photo-link-url'])
                                    ? $post_params['photo-link-url']
                                    : $post['photo-link-url'];
        } else {
            $params['caption'] = isset($post_params['photo-caption'])
                                    ? $post_params['photo-caption']
                                    : '';
            $params['tags'] = isset($post_params['tags'])
                                ? implode(",", $post_params['tags'])
                                : "";
            $params['click-through-url'] = isset($post_params['click-through-url'])
                                    ? $post_params['click-through-url']
                                    : '';
        }
        return $this->do_logged_request($api_url,$params);
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

        return array('status' => $status,
                     'result' => $result);
    }

    public function get_tumblr_name() {
        return $this->tumblr_name;
    }

    public function set_tumblr_name($tumblr_name) {
        $this->tumblr_name = $tumblr_name;
    }

    function delete_post($post_id) {
        return $this->do_logged_request($this->get_api_url('/post/delete'),
                    array(
                        'id'   => $post_id,
                    ));
    }

    function publish_post($post_id, $params) {
        $all_params = array(
                        'id'   => $post_id,
                  );
        if (isset($params)) {
            $all_params = array_merge($params, $all_params);
        }
        // override any state set on params
        $all_params['state'] = 'published';

        return $this->do_logged_request($this->get_api_url('/post/edit'), $all_params);
    }

    function create_post($params) {
        $all_params = array();
        if (isset($params)) {
            $all_params = array_merge($params, $all_params);
        }

        return $this->do_logged_request($this->get_api_url('/post'), $all_params);
    }

    function get_tumblr_list() {
        $api_url = 'http://api.tumblr.com/v2/user/info';

        $info = $this->do_logged_request($api_url, array());
        $response = json_decode($info['result'], true);
        $response = $response['response'];
        $user = $response['user'];
        
        return $user['blogs'];
    }
}

class tumblr extends abstract_tumblr {
    private $email;
    private $password;
    private $login_params;

    function do_logged_request($url, $params) {
        return self::do_request($url,
            array_merge($params, $this->login_params));
    }

    function tumblr($email, $password, $tumblr_name) {
        $this->email = $email;
        $this->password = $password;
        $this->tumblr_name = $tumblr_name;
        $this->login_params = array(
                            'email'     => $this->email,
                            'password'  => $this->password);
    }
}

class tumblr_oauth extends abstract_tumblr {
    private $consumer;
    private $access_token;
    private $oauth_token;
    private $sig_method;
    private $oauth_params; // array containing parameters to pass to oauth request

    function tumblr_oauth($oauth_token, $oauth_token_secret, $tumblr_name) {
        $this->sig_method = new OAuthSignatureMethod_HMAC_SHA1();

        $this->consumer = new OAuthConsumer(OAUTH_CONSUMER_KEY, OAUTH_SECRET_KEY, NULL);
        $this->access_token = new OAuthConsumer($oauth_token, $oauth_token_secret);
        $this->oauth_token = $oauth_token;
        $this->tumblr_name = $tumblr_name;

        $this->oauth_params = array('oauth_token' => $this->oauth_token);
    }

    protected static function executeOAuthRequest($oauth_req) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $oauth_req->to_url());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $oauth_req->to_postdata());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, explode(',', $oauth_req->to_header()));
        $res = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return array('status' => $status, 'result' => $res);
    }

    function do_logged_request($url, $params) {
        $params = array_merge($params, $this->oauth_params);

        $req = OAuthRequest::from_consumer_and_token($this->consumer, $this->access_token, 'POST', $url, $params);
        $req->sign_request($this->sig_method, $this->consumer, $this->access_token);

        return self::executeOAuthRequest($req);
    }

    protected static function oauth_request($url, $consumer, $token, $params, $parse_response = true, $http_method = 'POST') {
        $sig_method = new OAuthSignatureMethod_HMAC_SHA1();

        $req = OAuthRequest::from_consumer_and_token($consumer, $token, $http_method, $url, $params);
        $req->sign_request($sig_method, $consumer, $token);

        $response = self::executeOAuthRequest($req);

        if ($parse_response) {
            $result = array();
            parse_str($response['result'], $result);

            return $result;
        }

        return $response['result'];
    }

    /**
     * Make an authorize oAuth request
     * @param params array containing paramenters to pass to authorize request
     * @return the authorize url to call
     */
    static function authorize($params) {
        $test_consumer = new OAuthConsumer(OAUTH_CONSUMER_KEY, OAUTH_SECRET_KEY, NULL);
        $result = self::oauth_request(REQUEST_TOKEN_URL, $test_consumer, NULL, $params);

        $oauth_token = $result['oauth_token'];
        $oauth_token_secret = $result['oauth_token_secret'];
        $_SESSION[REQUEST_TOKEN] = $oauth_token;
        $_SESSION[REQUEST_TOKEN_SECRET] = $oauth_token_secret;

        return AUTHORIZE_URL . '?oauth_token=' . $oauth_token;
    }

    static function access($params) {
        $request_token = $_SESSION[REQUEST_TOKEN];
        $request_token_secret = $_SESSION[REQUEST_TOKEN_SECRET];

        $test_consumer = new OAuthConsumer(OAUTH_CONSUMER_KEY, OAUTH_SECRET_KEY, NULL);
        $test_token = new OAuthConsumer($request_token, $request_token_secret);

        return self::oauth_request(ACCESS_TOKEN_URL, $test_consumer, $test_token, $params);
    }
}

?>