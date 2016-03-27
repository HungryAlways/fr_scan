<?php
$lock_tag_file = 'update_fr_blog_flag.lock';
$is_updating = "no";
$fdt = "none";

header('Content-Type: text/json');
if(file_exists($lock_tag_file )){
	$is_updating = "yes";
    $fdt = file_get_contents($lock_tag_file);
}

$state = array(
	"is_updating" => $is_updating,
	"fdt" => $fdt
);

echo(json_encode($state));
?>