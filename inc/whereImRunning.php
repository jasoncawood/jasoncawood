<?php
date_default_timezone_set('America/Los_Angeles');

function plural($num) 
{
    if ($num != 1){ return "s"; }
}

function getRelativeTime($date) 
{
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

// so far just echo the result.
echo ('On Oct 22, 2013 ran 2.57mi');
?>
