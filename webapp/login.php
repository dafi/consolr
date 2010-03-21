<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
    <title>Tumblr Consolr</title>

    <link href="css/consolr.css" type="text/css" rel="stylesheet"/>

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
    <div class="login-container">
        <h1>Tumblr Consolr</h1>
        <form id="loginForm" action="doLogin.php" method="post">
            <p>
                <label for="email">Email address</label>
                <br/>
                <input type="text" id="email" name="email" class="input-text"/>
                <span id="email-error"></span>
            </p>
    
            <p>
                <label for="password">Password</label>
                <br/>
                <input type="password" id="password" name="password" class="input-text"/>
                <span id="password-error"></span>
            </p>
    
            <p>
                <label for="tumblrName">Tumblr name</label>
                <br/>
                <input type="text" id="tumblrName" name="tumblrName" class="input-text tumblr-name"/><span>.tumblr.com</span>
                <span id="tumblrName-error"></span>
            </p>
            <div>
                <input id="submit" type="submit" value="Log in"/>
            </div>
        </form>
    </div>
</body>
</html>
