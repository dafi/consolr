<?php
require_once 'inc/dbconfig.php';
require_once 'lib/db.php';
require_once 'lib/loginUtils.php';

function getFullRequestUrl() {
    return "http://" . $_SERVER[HTTP_HOST] . substr($_SERVER[REQUEST_URI], 0, strrpos($_SERVER[REQUEST_URI], "/") + 1);	
}

if (login_utils::is_logged()) {
    header("location: home.php");
}

if (isset($_GET['enter'])) {
    $params = array(
        'oauth_callback' => getFullRequestUrl() . 'login.php?access'
        );
    header("location: " . tumblr_oauth::authorize($params));
}

if (isset($_GET['access'])) {
    $params = array(
                'oauth_token'    => $_GET['oauth_token'],
                'oauth_verifier' => $_GET['oauth_verifier']
            );

    $result = tumblr_oauth::access($params);

    if (isset($result['oauth_token'])
        && login_utils::login_oauth($result['oauth_token'], $result['oauth_token_secret'], null)) {
        if (login_utils::after_login()) {
            header("location: home.php");
        } else {
            die("Unable to find a tumblr");
        }
    } else {
        die("Permission Denied");
    }
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
    <title>Tumblr Consolr</title>

    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico"/>

    <link type="text/css" href="css/consolr.css" rel="stylesheet"/>
    <link type="text/css" href="css/consolr/jquery-ui.css" rel="stylesheet" />

</head>
    <body>
        <noscript>
            <div class="ui-state-error">
                <a href="https://www.google.com/adsense/support/bin/answer.py?hl=en&amp;answer=12654">Javascript</a> is required to view this site.
            </div>
        </noscript>

        <?php include('inc/menu.php') ?>

    <div class="login-container">
        <div class="ui-corner-all ui-state-highlight">
            <h1 style="padding: 0pt 0.7em; margin-top: 20px;">Consolr</h1>
        </div>
        <br>
        <a style="padding: 0.7em; margin-top: 20px; background-color:white; font-size:14px; color: #000" href="login.php?enter">Sign in with tumblr</a>
    </div>

    <?php include('inc/footer.php'); ?>
    </body>
</html>
