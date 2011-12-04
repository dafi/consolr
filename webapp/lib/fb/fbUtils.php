<?php
class fb_utils {
    static function simple_publish_photo($fb_uid, $access_token, $post, $caption) {
        $alt_size = tumblr_utils::get_photo_by_width($post['photos'], 100);
        return tumblr::do_request('https://graph.facebook.com/' . $fb_uid . '/feed',
                                    array(
                                        'access_token' => $access_token,
                                        'name' => $post['tags'][0],
                                        'description' => $caption,
                                        'picture' => $alt_size['url'],
                                        'link' => $post['post_url']
                                    ));
    }
}
?>