<?php
$rejected_count = 0;
$unplanned_count = 0;
$duplicate_count = 0;
$new_feature_count = 0;
$broken_count = 0;
$never_test_count = 0;
$improve_count = 0;
$other_count = 0;

function is_skip_phase($dip){
  if(($dip == "FT") || ($dip == "SST") || ($dip == "Build") || ($dip == "SCA") || ($dip == "A&D")
	|| ($dip == "COD") || ($dip == "COR") || ($dip == "Purify")){
	  return true;
  }
	
  return false;
}

function count_fr_with_filter($rel, $fdt){
  global $rejected_count;
  global $unplanned_count;
  global $new_feature_count;
  global $broken_count;
  global $never_test_count;
  global $duplicate_count;
  global $improve_count;
  global $other_count;

  $con = mysql_connect("localhost","root","123456");
  if (!$con)
  {
    die('Could not connect: ' . mysql_error());
  }
  mysql_select_db("fr_db", $con);
  
  if(($rel == "none") && ($fdt != "domain"))
      $result = mysql_query("SELECT * FROM FrClass WHERE fdt = " . "'". "$fdt" . "'", $con);
  elseif(($rel == "none") && ($fdt == "domain"))
	  $result = mysql_query("SELECT * FROM FrClass", $con);
  elseif(($rel != "none") && ($fdt != "domain"))
	  $result = mysql_query("SELECT * FROM FrClass WHERE fdt = " . "'". "$fdt" . "'". " AND plan_rel = " . "'". "$rel" . "'", $con);
  elseif(($rel != "none") && ($fdt == "domain"))
	  $result = mysql_query("SELECT * FROM FrClass WHERE plan_rel = "  . "'". "$rel" . "'", $con);
  else
      $result = mysql_query("SELECT * FROM FrClass", $con);

  while($row = mysql_fetch_array($result))
  {
	  if(is_skip_phase($row["dip"]))
		  continue;
	  if($row["state"] == "Rejected"){
        $rejected_count++;
		continue;
      }
	  if($row["state"] == "Unplanned"){
        $unplanned_count++;
		continue;
      }	  
	  if($row["state"] == "Duplicate"){
        $duplicate_count++;
		continue;
      }	  	  
	  if(($row["state"] == "Delivered") || ($row["state"] == "Build") || ($row["state"] == "Resolved") || ($row["state"] == "Verified")){
        if((strncmp($row["rcr"],"ALU",3) == 0)) {
          $new_feature_count++;
          continue;
		}
		elseif($row["reason"] == "Broken Functionality"){
			$broken_count++;
			continue;
		}
		elseif($row["reason"] == "Never Tested Before"){
			$never_test_count++;
			continue;
		}
		elseif($row["reason"] == "Improvement/No bug"){
			$improve_count++;
			continue;
		}
		else
			$other_count++;
	  }
  }
  mysql_close($con);
}


//Main Entry
//Filter options
if(isset($_GET["plan_rel"]))
	$rel_filter = $_GET["plan_rel"];
else
   $rel_filter = "none";

if(isset($_GET["fdt"]))
	$fdt_filter = $_GET["fdt"];
else
   $fdt_filter = "domain";

header('Content-Type: text/json');
count_fr_with_filter($rel_filter, $fdt_filter);
$stats = array(
	array("name" => "Rejected","y" => $rejected_count),
	array("name" => "Unplanned",	"y" => $unplanned_count),
    array("name" => "Duplicated",	"y" => $duplicate_count),
	array("name" => "New Feature Introduced",	"y" => $new_feature_count),
	array("name" => "Broken Functionality",	"y" => $broken_count),
	array("name" => "Never Tested Before",	"y" => $never_test_count),
    array("name" => "Improvement/Not Bug",	"y" => $improve_count),
    array("name" => "Others",	"y" => $other_count)
);

echo(json_encode($stats));
?>


