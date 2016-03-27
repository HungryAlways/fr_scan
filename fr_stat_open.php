<?php
$category = array(
				"this week" => array("1-Critical" => 0, "2-Major" => 0, "3-Minor" =>0),
				"not take" => array("1-Critical" => 0, "2-Major" => 0, "3-Minor" =>0),
				"clarify" => array("1-Critical" => 0, "2-Major" => 0, "3-Minor" =>0),
				"not reproduced by sw" => array("1-Critical" => 0, "2-Major" => 0, "3-Minor" =>0),
				"not reproduced by submitter" => array("1-Critical" => 0, "2-Major" => 0, "3-Minor" =>0),
				"unknown" => array("1-Critical" => 0, "2-Major" => 0, "3-Minor" =>0),
				 "Total" => array("1-Critical" => 0, "2-Major" => 0, "3-Minor" =>0),
				"third party" => array("1-Critical" => 0, "2-Major" => 0, "3-Minor" =>0),
				"new incoming" => array("1-Critical" => 0, "2-Major" => 0, "3-Minor" =>0)
				 );
$category_got = array("Total");
$category_got_counter = array("Total" => array("1-Critical" => 0, "2-Major" => 0, "3-Minor" =>0));
function get_category($cat, $sev){
	global $category_got;
	global $category_got_counter;
	$is_exist = false;
	if($cat == null)
		$cat = "Unknown";

	foreach($category_got as $val){
		if($val == $cat){
			$is_exist = true;
			break;
		}
	}
	
	if(!$is_exist){
		$category_got[] = $cat;
		$category_got_counter["$cat"] = array("1-Critical" => 0, "2-Major" => 0, "3-Minor" =>0);
	}
    $category_got_counter["$cat"]["$sev"]++;
	$category_got_counter["Total"]["$sev"]++;
}

function inc_category($cat, $sev){
	global $category;
	$flag = false;
	if($cat == "")
		$cat = "unknown";

	foreach ($category as $key => $value){
		if($key == $cat){
			$category["$key"]["$sev"]++;
			$flag = true;
			break;
		}
	}
	
	if($flag == false)
		$category["unknown"]["$sev"]++;
	
	$category["Total"]["$sev"]++;
}

function count_fr_with_filter($rel, $fdt){
  global $category;

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
	  if($fdt == "domain"){
		if(($row["fdt"] != "1252") && ($row["fdt"] != "1251") && ($row["fdt"] != "1356") && ($row["fdt"] != "1343") && ($row["fdt"] != "BCMBL"))
		 continue;
		if(($row["fdt"] == "BCMBL") && ($row["ia"] != "dingjun he"))
		 continue;
	  }

	  if(($row["state"] == "New") || ($row["state"] == "Accepted") || ($row["state"] == "Hold") ||($row["state"] == "Query")){
		get_category($row["target_date"], $row["severity"]);
		inc_category($row["target_date"], $row["severity"]);
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
$data_critical = array();
$data_major = array();
$data_minor = array();

foreach($category_got_counter as $counter){
	$data_critical[] = $counter["1-Critical"];
	$data_major[] = $counter["2-Major"];
	$data_minor[] = $counter["3-Minor"];
}

$return_data = array(
	"categories" => $category_got,
	"categories_old" => array("Total", "To be closed by this week", "To be clarified" , "It can not be reproduced by SW" , "It can not be reproduced by submitter" , "3rd Party Issue", "Will not be taken in this release", "Newly incoming", "Unknown"),
	"series" => array( array("name" => "1-Critical", "data" => $data_critical),
					    array("name" => "2-Major",	 "data" => $data_major),
					  array("name" => "3-Minor", "data" => $data_minor)
					  ),
	"series_old" => array( array("name" => "1-Critical",
							 "data" => array($category["Total"]["1-Critical"],
											 $category["this week"]["1-Critical"],	
											$category["clarify"]["1-Critical"],
											$category["not reproduced by sw"]["1-Critical"],
											$category["not reproduced by submitter"]["1-Critical"],											 
											 $category["third party"]["1-Critical"],
										 $category["not take"]["1-Critical"],
											 $category["new incoming"]["1-Critical"],
											$category["unknown"]["1-Critical"])),
					  array("name" => "2-Major",
							 "data" => array($category["Total"]["2-Major"],
											 $category["this week"]["2-Major"],	
											$category["clarify"]["2-Major"],
											$category["not reproduced by sw"]["2-Major"],
											$category["not reproduced by submitter"]["2-Major"],		
											 $category["third party"]["2-Major"],
											 $category["not take"]["2-Major"],
											 $category["new incoming"]["2-Major"],
											$category["unknown"]["2-Major"])),
					  array("name" => "3-Minor",
							 "data" => array($category["Total"]["3-Minor"],
											 $category["this week"]["3-Minor"],	
											$category["clarify"]["3-Minor"],
											$category["not reproduced by sw"]["3-Minor"],
											$category["not reproduced by submitter"]["3-Minor"],		
											 $category["third party"]["3-Minor"],
											 $category["not take"]["3-Minor"],
											$category["new incoming"]["3-Minor"],
											$category["unknown"]["3-Minor"]))
					  )
				      );

echo(json_encode($return_data));
?>


