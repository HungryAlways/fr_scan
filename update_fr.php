<?php
function update_fr_comment($fr_id, $comment){
  $con = mysql_connect("localhost","root","123456");
  if (!$con)
  {
    die('Could not connect: ' . mysql_error());
  }
  mysql_select_db("fr_db", $con);

  mysql_query("UPDATE FrClass SET comment = '$comment' WHERE fr_id = '$fr_id'", $con);

  mysql_close($con);
}
function update_fr_cq($fr_id, $comment){
  $cookie_jar = dirname(__FILE__)."/pic.cookie";
  $url = "http://destgsu0628.de.alcatel-lucent.com/cqweb/cqartifactdetails.cq";
  $post_data = array ("action" => "RecordCommit", 
					  "Fields" => '[{"FieldName": "DetailedRCAReport","FieldValue":["Document here in detail the results of the RCA by the Feature Team (mandatory for System Test and FCU FRs).","For sev 1 FCU FRs, the RCA must be documented in a WebLib document which is referred from here.","What is the root cause of this FR, i.e. Why was it introduced ?", ""]}]',
					  "state" => "MODIFY", 
					  "resourceId" => "cq.record%3AFR_IR%2F" . $fr_id . "%40prod%2FALU", 
					  "cquid" => "rwQJ7z0QuIkUhFL2QDYzHeC");

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
  curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
  curl_exec($ch);
  curl_close($ch);
}

//Main Entry
if(isset($_GET["fr_id"]) && isset($_GET["texta"])){
  update_fr_comment($_GET["fr_id"], $_GET["texta"]);
//  update_fr_cq($_GET["fr_id"], $_GET["texta"]);
  echo $_GET["fr_id"] . " is updated successfully!";
}
else
{
  echo $_GET["fr_id"] . " is updated failed!";
}

?>
