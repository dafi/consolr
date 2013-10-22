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
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $res = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $res;
}

if (count($argv) == 1) {
    echo "Tumblr blog name must be specified\n";
    return;
}

$tumblr_name = $argv[1];
$output_file = "missing_birthdays.sql";

$sql = "INSERT INTO CONSOLR_BIRTHDAY (name, birth_date, tumblr_name) VALUES";

$missing_birth_days = consolr_db::get_missing_birth_days();

$sql_lines = array();

$total = count($missing_birth_days);
$count = 1;
foreach ($missing_birth_days as $name) {
    $clean_name = str_replace(' ','_', $name);
    $clean_name = str_replace('"','', $clean_name);
    $url = 'http://en.wikipedia.org/wiki/' . $clean_name;
    $text = read_url($url);
    $doc = new DOMDocument('1.0', 'UTF-8');

    echo $count . "/" . $total . " " . $clean_name . "\n";
    ++$count;

    if ($doc->loadHTML($text)) {
        $xpath = new DOMXPath($doc);
        $entries = $xpath->query('//*[@class="bday"]');
        $birth_date = null;
        if ($entries->length == 1) {
            $birth_date = $entries->item(0)->nodeValue;
            // escape single quotes for MySQL
            $name = str_replace("'", "\'", $name);
            array_push($sql_lines, sprintf("('%s', '%s', '%s')", $name, $birth_date, $tumblr_name));
        }
    }
}

file_put_contents($output_file, $sql . "\n" . implode($sql_lines, ",\n"));
echo "Written in $output_file\n";
?>
