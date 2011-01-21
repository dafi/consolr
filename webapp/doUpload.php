<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';
require_once 'inc/dbconfig.php';
require_once 'lib/db.php';

define("CONSOLR_UPLOAD_OK", 0);
define("CONSOLR_UPLOAD_ERR_URL_MANDATORY", 1);
define("CONSOLR_UPLOAD_ERR_INVALID_DATE_FORMAT", 2);

// There is an upload error and the message is generic,
// the msg field contains the error description
define("CONSOLR_UPLOAD_ERR_GENERIC", 3);

$tumblr = login_utils::get_tumblr();

/**
 * Post photo by url
 * @return array (status, msg) msg is valid only for some statuses
 */
function queue_photo_by_url($url, $caption, $str_time, $tags, $tumblr) {
    if (!isset($url)) {
        return array('status' => CONSOLR_UPLOAD_ERR_URL_MANDATORY);
    }
    $time = strtotime($str_time);
    if ($time === false) {
        return array('status' => CONSOLR_UPLOAD_ERR_INVALID_DATE_FORMAT, 'msg', $str_time);
    }

    $results = $tumblr->post_photo_to_queue($url,
                                            $caption,
                                            $str_time,
                                            explode(",", $tags));
    //$results = array('status' => '201', 'result' => 'TEST WITHOUT REAL POST');
    if ($results['status'] != 201) {
        return array('status' => CONSOLR_UPLOAD_ERR_GENERIC, 'msg' => $results['result']);
    }
    return array('status' => CONSOLR_UPLOAD_OK);
}

function draft_photo_by_url($url, $caption, $tags, $tumblr) {
    if (!isset($url)) {
        return array('status' => CONSOLR_UPLOAD_ERR_URL_MANDATORY);
    }

    $params = array(
                    'state'     => 'draft',
                    'type'      => 'photo',
                    'source'    => $url,
                    'caption'   => $caption,
                    'tags'      => $tags ? $tags : ''
                   );

    $results = $tumblr->create_post($params);
    if ($results['status'] != 201) {
        return array('status' => CONSOLR_UPLOAD_ERR_GENERIC, 'msg' => $results['result']);
    }
    return array('status' => CONSOLR_UPLOAD_OK);
}

$url = $_POST['url'];
$caption = $_POST['caption'];
$date = $_POST['date'];
$tags = $_POST['tags'];
$state = $_POST['state'];

// do not add here but on doPublish so the selection can contain more photos
$addSeeMore = false;

if ($addSeeMore) {
    $title = '<p>&nbsp;</p><p><strong>See More</strong></p>';
    $see_more_tags = explode(",", $tags);
    // use only the first tag
    $see_more_tags = array($see_more_tags[0]);
    $caption .= tumblr_utils::get_see_more_html($tumblr, $title, $see_more_tags, 3, 3);
}

if ($state == "queue") {
    $results = queue_photo_by_url($url, $caption, $date, $tags, $tumblr);
} else if ($state == "draft") {
    $results = draft_photo_by_url($url, $caption, $tags, $tumblr);
} else {
    $results = array('status' => CONSOLR_UPLOAD_ERR_GENERIC, 'msg' => 'Invalid state');
}
//$results = array('status' => (rand() % 2) != 0 ? CONSOLR_UPLOAD_OK : CONSOLR_UPLOAD_ERR_GENERIC, 'msg' => 'TEST WITHOUT REAL POST');
$msg;
switch ($results['status']) {
    case CONSOLR_UPLOAD_OK:
        break;
    case CONSOLR_UPLOAD_ERR_URL_MANDATORY:
        $msg = 'Url is mandatory';
        break;
    case CONSOLR_UPLOAD_ERR_INVALID_DATE_FORMAT:
        $msg = 'Invalid date format ' . $results['msg'];
        break;
    case CONSOLR_UPLOAD_ERR_GENERIC:
        $msg = $results['msg'];
        break;
}

if (isset($msg)) {
    header("HTTP/1.x 400 " . $msg);
}
?>