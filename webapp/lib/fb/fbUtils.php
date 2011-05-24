<?php
class fb_utils {
    static function simple_publish_photo($fb_uid, $access_token, $post, $caption) {
        return tumblr::do_request('https://graph.facebook.com/' . $fb_uid . '/feed',
                                    array(
                                        'access_token' => $access_token,
                                        'name' => $post['tags'][0],
                                        'description' => $caption,
                                        'picture' => $post['photo-url-100'],
                                        'link' => $post['url']
                                    ));
    }
}
?>