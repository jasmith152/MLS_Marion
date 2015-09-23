<?php
$time = microtime();
$time = explode(' ', $time);
$begintime = $time[1] + $time[0];
for($i=0;$i<10000;$i++)
    file_exists('/home/naturmls/public_html/index.php');
$time = microtime();
$time = explode(" ", $time);
$endtime = $time[1] + $time[0];
$totaltime = ($endtime - $begintime);
echo 'PHP parsed this in ' .$totaltime. ' seconds using file_exists.</br>';
$time = microtime();
$time = explode(" ", $time);
$begintime = $time[1] + $time[0];
for($i=0;$i<10000;$i++)
    is_file('/home/naturmls/public_html/index.php');
$time = microtime();
$time = explode(" ", $time);
$endtime = $time[1] + $time[0];
$totaltime = ($endtime - $begintime);
echo 'PHP parsed this in ' .$totaltime. ' seconds using is_file.</br>';
