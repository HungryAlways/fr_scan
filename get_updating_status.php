<?php
$lock_tag_file = 'update_fr_blog_flag.lock';
$last_update_time_file = "last_update_time.log";
$is_updating = "no";
$fdt = "none";

header('Content-Type: text/json');
if(file_exists($lock_tag_file )){
	$is_updating = "yes";
    $fdt = file_get_contents($lock_tag_file);
}

if(file_exists($last_update_time_file )){
    $last_time = file_get_contents($last_update_time_file);
}

$state = array(
	"is_updating" => $is_updating,
	"fdt" => $fdt,
    "last_time" => $last_time
);

echo(json_encode($state));
?>