<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';

$tumblr = login_utils::get_tumblr();
?>
<html>
    <head>
        <title>Consolr - Queue Multiple Photos</title>

        <link href="css/consolr.css" type="text/css" rel="stylesheet"/>

    </head>
    <body>
        <div style="text-align: right">
            <a href="multiq.php">Multiple Queue</a>
            |
            <a href="queue.php">Queue</a>
            |
            <a href="logout.php">[<?php echo $tumblr->get_tumblr_name() ?>] Logout</a>
        </div>

        <form id="postForm" method="post" action="doMultiq.php">
            <fieldset>
                <legend>Photo 1</legend>
                <label for="url1">Url</label>
                <br/>
                <input type="text" name="url1" id="url1" size="100"/>
                <br/>

                <label for="caption1">Caption</label>
                <br/>
                <input type="text" name="caption1" id="caption1" size="100"/>
                <br/>
                
                <label for="date1">Date</label>
                <br/>
                <input type="text" name="date1" id="date1" size="50"/>
                <br/>
                
                <label for="tags1">Tags</label>
                <br/>
                <input type="text" name="tags1" id="tags1" size="50"/>
                <br/>
            </fieldset>

            <fieldset>
                <legend>Photo 2</legend>
                <label for="url2">Url</label>
                <br/>
                <input type="text" name="url2" id="url2" size="100"/>
                <br/>

                <label for="caption2">Caption</label>
                <br/>
                <input type="text" name="caption2" id="caption2" size="100"/>
                <br/>
                
                <label for="date2">Date</label>
                <br/>
                <input type="text" name="date2" id="date2" size="50"/>
                <br/>
                
                <label for="tags2">Tags</label>
                <br/>
                <input type="text" name="tags2" id="tags2" size="50"/>
                <br/>
            </fieldset>
            
            <div>
                <input type="submit" value="Insert Photos"/>
            </div>
        </form>
    </body>
</html>
