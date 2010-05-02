<?php
require_once 'lib/loginUtils.php';

if (login_utils::is_logged()) {
    header ("location: queue.php");
    return;
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

    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.validate.js"></script>
    <script type="text/javascript">
      $(function() {
        $('#loginForm').validate({
            rules: {
              email: "required",
              password: "required",
              tumblrName: "required"
            },
            messages: {
              email: "Please specify the email",
              password: "Please specify the password",
              tumblrName: "Please specify the tumblr name"
            },
            submitHandler: function(form) {
                form.submit();
            },
            errorPlacement: function(error, element) {
                var errorEl = $("#" + element.attr("id") + "-error");
                error.appendTo(errorEl);
            }
          });


         $('#email').focus();
      });
    </script>
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
        <form id="loginForm" action="doLogin.php" method="post">
            <p>
                <label for="email">Email address</label>
                <br/>
                <input type="text" id="email" name="email" class="input-text ui-widget-content"/>
                <span id="email-error"></span>
            </p>

            <p>
                <label for="password">Password</label>
                <br/>
                <input type="password" id="password" name="password" class="input-text ui-widget-content"/>
                <span id="password-error"></span>
            </p>

            <p>
                <label for="tumblrName">Tumblr name</label>
                <br/>
                <input type="text" id="tumblrName" name="tumblrName" class="input-text tumblr-name ui-widget-content"/><span>.tumblr.com</span>
                <span id="tumblrName-error"></span>
            </p>

            <div class="ui-dialog-buttonpane ui-helper-clearfix button-box">
                <input id="submitForm" type="submit" value="Log in" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only ui-state-focus"/>
            </div>
        </form>
    </div>

    <?php include('inc/footer.php'); ?>
    </body>
</html>
