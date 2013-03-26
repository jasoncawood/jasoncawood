<?php
date_default_timezone_set('America/Los_Angeles');

function plural($num) {
    if ($num != 1){ return "s"; }
}

function getRelativeTime($date) {
    $diff = $date-time();
    if ($diff<60)
        return $diff . " second" . plural($diff);
    $diff = round($diff/60);
    if ($diff<60)
        return $diff . " minute" . plural($diff);
    $diff = round($diff/60);
    if ($diff<24)
        return $diff . " hour" . plural($diff);
    $diff = round($diff/24);
    if ($diff<7)
        return $diff . " day" . plural($diff);
    $diff = round($diff/7);
    if ($diff<4)
        return $diff . " week" . plural($diff);
    return "on " . date("F j, Y", strtotime($date));
}

$use_cache=false;
$debug_mode=false;
$dateformat="j F Y"; // 10 March 2009 - see http://www.php.net/date for details
$timeformat="g.ia"; // 12.15am
$items_to_show=1;
$event_error="<p>I have nothing planned today.</p>";

$calendarfeed = "Your_Private_Calendar_URL";

if ($debug_mode) {
    error_reporting (E_ALL);
    ini_set('display_errors', 1);
    ini_set('error_reporting', E_ALL);
    echo "<P>Debug mode is on. Hello there.<BR>Your server thinks the time is ".date(DATE_RFC822)."</p>";
}

$calendar_xml_address = str_replace("/basic","/full?singleevents=true&futureevents=true&max-results=".$items_to_show."&orderby=starttime&sortorder=a",$calendarfeed);

if ($debug_mode) {
    echo "<P>We're going to go and grab <a href='$calendar_xml_address'>this feed</a>.<P>";
}

if ($use_cache) {

    $cache_time = 3600*12; // 12 hours
    $cache_file = $_SERVER['DOCUMENT_ROOT'].'/gcal.xml'; //xml file saved on server

    if ($debug_mode) {echo "<P>Your cache is saved at ".$cache_file."</P>";}

    $timedif = @(time() - filemtime($cache_file));

    $xml = "";
    if (file_exists($cache_file) && $timedif < $cache_time) {
        if ($debug_mode) {echo "<P>I'll use the cache.</P>";}
        $str = file_get_contents($cache_file);
        $xml = simplexml_load_string($str);
    } else { //not here
        if ($debug_mode) {echo "<P>I don't have any valid cached copy.</P>";}
        $xml = simplexml_load_file($calendar_xml_address);
        if ($f = fopen($cache_file, 'w')) {
            $str = $xml->asXML();
            fwrite ($f, $str, strlen($str));
            fclose($f);
            if ($debug_mode) {echo "<P>Cache saved :)</P>";}
        } else { echo "<P>Can't write to the cache.</P>"; }
    }

} else {
    $xml = simplexml_load_file($calendar_xml_address);
}

if ($debug_mode) {echo "<P>Successfully got the GCal feed.</p>";}
$items_shown=0;
$old_date="";
$xml->asXML();

foreach ($xml->entry as $entry){
    $ns_gd = $entry->children('http://schemas.google.com/g/2005');

    if ($debug_mode) { echo "<P>Here's the next item's start time... GCal says ".$ns_gd->when->attributes()->startTime." PHP says ".date("g.ia  -Z",strtotime($ns_gd->when->attributes()->startTime))."</p>"; }

    $nowCalDate    = date($dateformat, time());
    $nowCalTime    = date($timeformat, time());
    $gCalStartDiff = (strtotime($ns_gd->when->attributes()->startTime)-time());
    $gCalEndDiff   = (strtotime($ns_gd->when->attributes()->endTime)-time());
    $gCalDateStart = date($dateformat, strtotime($ns_gd->when->attributes()->startTime));
    $gCalDateEnd   = date($dateformat, strtotime($ns_gd->when->attributes()->endTime));
    $gCalStartTime = date($timeformat, strtotime($ns_gd->when->attributes()->startTime));
    $gCalEndTime   = date($timeformat, strtotime($ns_gd->when->attributes()->endTime));

    if ($items_to_show>0 && $items_shown<$items_to_show && ($gCalDateStart==$nowCalDate || $gCalDateEnd==$nowCalDate)) {
        if ($gCalStartDiff>0){
            //free for the next ?
            echo ('I am available for the next ').getRelativeTime(strtotime($ns_gd->when->attributes()->startTime));
        } else if ($gCalEndDiff>0) {
            //Busy for the next ?
            echo ('I am busy for the next ').getRelativeTime(strtotime($ns_gd->when->attributes()->endTime));
        }
        $items_shown++;
    }
}

if (!$items_shown) { echo $event_error; }
?>