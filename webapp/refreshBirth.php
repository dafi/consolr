<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';
require 'inc/dbconfig.php';
require 'lib/db.php';

function read_url($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);

    $res = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $res;
}

$tumblr_name = login_utils::get_tumblr()->get_tumblr_name();

$sql ="INSERT INTO CONSOLR_BIRTHDAY (name, birth_date, tumblr_name) VALUES";

$missing_birth_days = consolr_db::get_missing_birth_days();

array();
$list = array();
$sql_lines = array();

foreach ($missing_birth_days as $name) {
    $clean_name = str_replace(' ','_', $name);
    $clean_name = str_replace('"','', $clean_name);
    $url = 'http://en.wikipedia.org/wiki/' . $clean_name;
    $text = read_url($url);
    $doc = new DOMDocument('1.0', 'UTF-8');
    
    if ($doc->loadHTML($text)) {
        $xpath = new DOMXPath($doc);
        $entries = $xpath->query('//*[@class="bday"]');
        $birth_date = null;
        if ($entries->length == 1) {
            $birth_date = $entries->item(0)->nodeValue;
            array_push($sql_lines, sprintf("('%s', '%s', '%s')", $name, $birth_date, $tumblr_name));
        }
        array_push($list, array('url' => $url,
                                'name' => $name,
                                'birth_date' => $birth_date));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>Consolr - Refresh Birthdays</title>
        <style>
        #list-names tfoot {
            text-align: center;
        }
        
        .birthdate-found {
            text-align: center;
            color: green;
        }
        
        .birthdate-not-found {
            text-align: center;
            color: red;
        }
        </style>
    </head>
    <body>
        <table id="list-names">
            <thead>
            <tr>
                <th>Name</th>
                <th>Birth date</th>
            </tr>
            </thead>
            <tbody>
                <?php
                    $found = 0;
                    foreach ($list as $item) { ?>
                <tr>
                    <td><a href="<?php echo $item['url'] ?>"><?php echo $item['name'] ?></a></td>
                    <td class="<?php echo $item['birth_date'] ? 'birthdate-found' : 'birthdate-not-found'?>"><?php
                    if ($item['birth_date']) {
                        ++$found;
                        echo $item['birth_date'];
                    } else {
                        echo '---';
                    }
                    ?></td>
                </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><?php echo 'Found ' . $found . '/' . count($list) ?></td>
                </tr>
            </tfoot>
        </table>
            
        <?php if (count($sql_lines)) { ?>
        <div>
            <textarea name="sqlScript" id="sqlScript" rows="50" cols="100"><?php echo $sql . "\n" . implode($sql_lines, ",\n") ?></textarea>
        </div>
        <?php } ?>
    </body>
</html>