<?php
function update_dart($fr_id, $dart){
  $con = mysql_connect("localhost","root","123456");
  if (!$con)
  {
    die('Could not connect: ' . mysql_error());
  }
  mysql_select_db("fr_db", $con);

  mysql_query("UPDATE FrClass SET dart = '$dart' WHERE fr_id = '$fr_id'", $con);

  mysql_close($con);
}
//Main Entry
if(isset($_GET["fr_id"]) && isset($_GET["texta"])){
  update_dart($_GET["fr_id"], $_GET["texta"]);
  echo $_GET["fr_id"] . " is updated successfully!";
}
else
{
  echo $_GET["fr_id"] . " is updated failed!";
}

?>
