<html>
<head>
<style type="text/css">
#customers
  {
  font-family:"Trebuchet MS", Arial, Helvetica, sans-serif;
  width:100%;
  border-collapse:collapse;
  }

#customers td, #customers th 
  {
  font-size:1em;
  border:1px solid #98bf21;
  padding:3px 7px 2px 7px;
  }

#customers th 
  {
  font-size:1.1em;
  text-align:left;
  padding-top:5px;
  padding-bottom:4px;
  background-color:#A7C942;
  color:#ffffff;
  }

#customers tr.alt td 
  {
  color:#000000;
  background-color:#EAF2D3;
  }
</style>
</head>

<body>
<?php
function set_fr_state_to_close(){
  $con = mysql_connect("localhost","root","123456");
  if (!$con)
  {
    die('Could not connect: ' . mysql_error());
  }
  mysql_select_db("fr_db", $con);

  mysql_query("UPDATE FrClass SET state = 'closed'", $con);

  mysql_close($con);
}

function insert_fr_to_db($fr_info){
  $con = mysql_connect("localhost","root","123456");
  if (!$con)
  {
    die('Could not connect: ' . mysql_error());
  }
  mysql_select_db("fr_db", $con);

  $fr_description = htmlentities($fr_info[4], ENT_QUOTES);
  if(strlen($fr_info[12]) <= 1)
    $fr_engineer = "";
  else
    $fr_engineer = $fr_info[12];
  
  $result = mysql_query("SELECT fr_id FROM FrClass WHERE fr_id = '$fr_info[0]'", $con);
  if(!mysql_fetch_array($result)){ 
     mysql_query("INSERT INTO FrClass (fr_id, sub_date, severity, state, brief_des, fdt, ia, orig_proj, plan_rel, diph, ec, domain, engineer) VALUES('$fr_info[0]', '$fr_info[1]','$fr_info[2]','$fr_info[3]','$fr_description','$fr_info[5]','$fr_info[6]','$fr_info[7]','$fr_info[8]','$fr_info[9]','$fr_info[10]','$fr_info[11]','$fr_engineer')", $con); 
  }
  else {
    mysql_query("UPDATE FrClass SET sub_date = '$fr_info[1]' WHERE fr_id = '$fr_info[0]'", $con);
    mysql_query("UPDATE FrClass SET severity = '$fr_info[2]' WHERE fr_id = '$fr_info[0]'", $con);
    mysql_query("UPDATE FrClass SET state = '$fr_info[3]' WHERE fr_id = '$fr_info[0]'", $con);
    mysql_query("UPDATE FrClass SET brief_des = '$fr_description' WHERE fr_id = '$fr_info[0]'", $con);
    mysql_query("UPDATE FrClass SET fdt = '$fr_info[5]' WHERE fr_id = '$fr_info[0]'", $con);
    mysql_query("UPDATE FrClass SET ia = '$fr_info[6]' WHERE fr_id = '$fr_info[0]'", $con);
    mysql_query("UPDATE FrClass SET orig_proj = '$fr_info[7]' WHERE fr_id = '$fr_info[0]'", $con);
    mysql_query("UPDATE FrClass SET plan_rel = '$fr_info[8]' WHERE fr_id = '$fr_info[0]'", $con);
    mysql_query("UPDATE FrClass SET diph = '$fr_info[9]' WHERE fr_id = '$fr_info[0]'", $con);
    mysql_query("UPDATE FrClass SET ec = '$fr_info[10]' WHERE fr_id = '$fr_info[0]'", $con);
    mysql_query("UPDATE FrClass SET domain = '$fr_info[11]' WHERE fr_id = '$fr_info[0]'", $con);
    mysql_query("UPDATE FrClass SET engineer = '$fr_engineer' WHERE fr_id = '$fr_info[0]'", $con);
  
  }

  mysql_close($con);
}


function parse_fr_file($file_name){
  $file=fopen($file_name,"r");
  $line_num = 0;
  $fr_array = array();

  echo "<table id='customers'>"; 
  while(!feof($file))
  {
     $line = fgets($file);
     if(strlen($line) <= 1)
         continue;

     $token = strtok($line, ";");
     if(($line % 2) == 1)
       echo "<tr>";
     else
       echo "<tr class='alt'>";

     $col = 0;
     while (($token !== false) && ($col < 13))
     {
       if($line_num == 0)
         echo "<th>" . "$token" . "</th>";
       else
         echo "<td>" . "$token" . "</td>";
       $fr_array[$col] = $token;
       $token = strtok(";");
       $col++;
     }
     
     echo "</tr>";
     if($line_num != 0)
       insert_fr_to_db($fr_array);
     $line_num++;
  }
  echo "</table>";
  $line_num--;
  echo "Total $line_num FR was added or updated!<br>";
  fclose($file);
}


if(isset($_FILES["file"]["name"])){
  if ($_FILES["file"]["error"] > 0)
  {
    echo "Error: " . $_FILES["file"]["error"] . "<br />";
  }
  else
  {
    set_fr_state_to_close();
    parse_fr_file($_FILES["file"]["tmp_name"]);
  }
}
else{
  echo "<form action='upload_file.php' method='post' enctype='multipart/form-data'>";
  echo "<label for='file'>Filename:</label>";
  echo "<input type='file' name='file' id='file' />"; 
  echo "<br/>";
  echo "<input type='submit' name='submit' value='Submit' />";
  echo "</form>";
}
?>

</body>
</html>
