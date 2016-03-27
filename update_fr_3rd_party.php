<?php
function update_fr_3rd_party($fr_id, $third_party){
  $con = mysql_connect("localhost","root","123456");
  if (!$con)
  {
    die('Could not connect: ' . mysql_error());
  }
  mysql_select_db("fr_db", $con);

  mysql_query("UPDATE FrClass SET 3rd_party = '$third_party' WHERE fr_id = '$fr_id'", $con);

  mysql_close($con);
}
//Main Entry
if(isset($_GET["fr_id"]) && isset($_GET["texta"])){
  update_fr_3rd_party($_GET["fr_id"], $_GET["texta"]);
  echo $_GET["fr_id"] . " is updated successfully!";
}
else
{
  echo $_GET["fr_id"] . " is updated failed!";
}

?>
