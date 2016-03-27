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
  font-size:0.8em;
  border:1px solid #98bf21;
  padding:3px 7px 2px 7px;
}

#customers th 
{
  font-size:0.8em;
  text-align:left;
  padding-top:5px;
  padding-bottom:4px;
  background-color:#A7C942;
  color:black;
}

tr.alt 
{
  color:#000000;
  background-color:#EAF2D3;
}

#customers textarea
{
  font-size:1.0em;
  font-family:"Trebuchet MS";
  border:1px solid #98bf21;
  padding:3px 7px 2px 7px;
}

tr:hover
{
background-color:#66CC99;
}

tr.alt:hover
{
background-color:#66CC99;
}

div
{
width:100px;
height:75px;
background-color:yellow;
border:1px solid black;
border-radius:5px;
box-shadow: 10px 10px 5px #888888;
text-shadow: 5px 5px 5px #FF0000;
}
div#div2
{
transform:rotate(10deg);
-ms-transform:rotate(10deg); /* IE 9 */
-moz-transform:rotate(10deg); /* Firefox */
-webkit-transform:rotate(10deg); /* Safari and Chrome */
-o-transform:rotate(10deg); /* Opera */

transition:10s;
-o-transition:10s;
-moz-transition:10s;
-webkit-transition:10s;
}
div#div2:hover
{
transform:rotate(360deg);
-ms-transform:rotate(360deg); /* IE 9 */
-moz-transform:rotate(360deg); /* Firefox */
-webkit-transform:rotate(360deg); /* Safari and Chrome */
-o-transform:rotate(360deg); /* Opera */
}
</style>

<script type="text/javascript">
function replaceAll(strOrg,strFind,strReplace){
 var index = 0;
 while(strOrg.indexOf(strFind,index) != -1){
  strOrg = strOrg.replace(strFind,strReplace);
  index = strOrg.indexOf(strFind,index);
 }
 return strOrg
} 

function eventOnUpdated(x)
{
  x.style.background="white";
}

function updateFRComment(x,fr)
{
  var xmlhttp;
  var comment;

  if(fr.length == 0) 
  {
    return;
  }

  if(window.XMLHttpRequest)
  {
    xmlhttp=new XMLHttpRequest();
  }
  else
  {
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

  xmlhttp.onreadystatechange=function()
  {
    if(xmlhttp.readyState==4 && xmlhttp.status==200)
    {
      //ok
      //alert(xmlhttp.responseText);
    }
  }
  comment = x.value; 
  comment = replaceAll(comment, "\n", "<br>");
  xmlhttp.open("GET","update_fr.php?fr_id="+fr+"&&texta="+comment,true);
  xmlhttp.send();
  eventOnUpdated(x);
}

function updateFRTarget(x,fr)
{
  var xmlhttp;
  var comment;

  if(fr.length == 0) 
  {
    return;
  }

  if(window.XMLHttpRequest)
  {
    xmlhttp=new XMLHttpRequest();
  }
  else
  {
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

  xmlhttp.onreadystatechange=function()
  {
    if(xmlhttp.readyState==4 && xmlhttp.status==200)
    {
      //ok
      //alert(xmlhttp.responseText);
    }
  }
  comment = x.value; 
  //comment.replace("\n","<br>");
  comment = replaceAll(comment, "\n", "<br>");
  xmlhttp.open("GET","update_fr_tgt_date.php?fr_id="+fr+"&&texta="+comment,true);
  xmlhttp.send();
  eventOnUpdated(x);
}

function eventOnChange(x,fr)
{
  x.style.background="yellow";
}

function copy_flow(dst, src){
var i;
for (i = 0; i < dst.cells.length;i++){
    dst.cells[i].innerHTML = src.cells[i].innerHTML;
}
}

function sort_table(lnk,clid) {
  var head = lnk.parentNode;
  var table = head.parentNode;
  var new_row;
  var j;
  var k;
  var cur_sort_key;
  var pre_sort_key;
  var table_length = table.rows.length;

  if(table_length <= 1){
	  alert(lnk.cellIndex);
	  return;
  }

  new_row = table.insertRow( table_length );
  for ( var i=0; i<head.cells.length; i++ ){
	  var objNewCell = new_row.insertCell(i);
  }

  pre_sort_key = table.rows[1].cells[lnk.cellIndex].innerHTML;
  for (var j = 2; j < table_length;j++) { 
	  cur_sort_key = table.rows[j].cells[lnk.cellIndex].innerHTML;
	  if(pre_sort_key == cur_sort_key){
		continue;
	  }

	  copy_flow(new_row, table.rows[j]);

      for (k = j + 1; k < table_length;k++) {
          if(table.rows[k].cells[lnk.cellIndex].innerHTML == pre_sort_key){
              copy_flow(table.rows[j], table.rows[k]);
              copy_flow(table.rows[k], new_row);
			  break;
		  }

	  }
      pre_sort_key = table.rows[j].cells[lnk.cellIndex].innerHTML;
  }

  table.deleteRow( table_length )
}
</script>

</head>

<body>
<?php
$severity_critical = 0;
$severity_major = 0;
$severity_minor = 0;
function list_table_head_col($id, $content, $is_sortable){
	$col_id = "+" . $id . "+";
	if($is_sortable)
		echo "<th id=$col_id onclick=\"sort_table(this, '$col_id');\" >" . $content . "</th>";
	else
	    echo "<th id=$col_id>" . $content . "</th>";
}

function list_table_head(){
  global $severity_critical;
  global $severity_major;
  global $severity_minor;
  $total_fr=$severity_critical+$severity_major+$severity_minor;

  echo "<tr>";
  list_table_head_col($col_id, "FR", 0);
  $col_id++;
  list_table_head_col($col_id, "Submitted", 0);
  $col_id++;
  list_table_head_col($col_id, "<div id='div2'>" . "<font color=red>" . "Critical:" . $severity_critical . "</font>" . "<br>" . "<font color=DeepPink>" . "Major:" . $severity_major . "</font>" . "<br>" . "<font color=green>" . "Minor:" . $severity_minor . "</font>" . "<br>" . "<font color=black>" . "Total:" . $total_fr . "</font>" . "</div>", 0);
  $col_id++;	
  list_table_head_col($col_id, "St", 0);
  $col_id++;
  list_table_head_col($col_id, "Brief Description", 0);
  $col_id++;	
  list_table_head_col($col_id, "PlanRel", 1);
  $col_id++;		
  list_table_head_col($col_id, "DIPh", 0);
  $col_id++;			
  list_table_head_col($col_id, "Engineer", 1);
  $col_id++;	
  list_table_head_col($col_id, "Target Date", 1);
  $col_id++;	
  list_table_head_col($col_id, "Comment");
  $col_id++;	

  echo "</tr>";
}

function list_one_fr($row, $row_alt){
  $fr_id = $row["fr_id"];
  $fr_comment = $row["comment"];
  $fr_target_date = $row["target_date"];

  if($row_alt % 2 == 0)
    echo "<tr>";
  else
    echo "<tr class='alt'>";
    
  echo "<td>" . $row['fr_id'] . "</td>";
  echo "<td>" . $row['sub_date'] . "</td>";
  echo "<td>" . $row['severity'] . "</td>";
  echo "<td>" . $row['state'] . "</td>";
  echo "<td>" . $row['brief_des'] . "</td>";
  echo "<td>" . $row['plan_rel'] . "</td>";
  echo "<td>" . $row['diph'] . "</td>";
  echo "<td>" . $row['engineer'] . "</td>";
  echo "<td>";
  $fr_target_date=str_replace("<br>","\n",$fr_target_date);
  echo "<textarea rows='1' cols='10' name='texta' id=$fr_id onclick=\"eventOnChange(this,'$fr_id');\" ondblclick=\"updateFRTarget(this,'$fr_id');\">$fr_target_date</textarea>";
  echo "</td>";
  echo "<td>";
  $fr_comment=str_replace("<br>","\n",$fr_comment);
  echo "<textarea rows='8' cols='32' name='texta' id=$fr_id onclick=\"eventOnChange(this,'$fr_id');\" ondblclick=\"updateFRComment(this,'$fr_id');\">$fr_comment</textarea>";
  echo "</td>";

  echo "</tr>";
}


function show_fr(){
  $pre_engineer = "";
  $con = mysql_connect("localhost","root","123456");
  if (!$con)
  {
    die('Could not connect: ' . mysql_error());
  }
  mysql_select_db("fr_db", $con);
  
  $result = mysql_query("SELECT * FROM FrClass ORDER BY severity,engineer", $con);
  
  echo "<table id='customers'>"; 
  
  list_table_head();
  
  $row_alt = 0;
  while($row = mysql_fetch_array($result))
  {
    if($row["state"] != "closed"){
      if($pre_engineer !=  $row["engineer"]){
        $row_alt++;
        $pre_engineer = $row["engineer"];
      }

      list_one_fr($row, $row_alt);
    }
  }
  echo "</table>";
  
  mysql_close($con);
}

function count_fr(){
  global $severity_critical;
  global $severity_major;
  global $severity_minor;

  $con = mysql_connect("localhost","root","123456");
  if (!$con)
  {
    die('Could not connect: ' . mysql_error());
  }
  mysql_select_db("fr_db", $con);

  $result = mysql_query("SELECT * FROM FrClass ORDER BY severity,engineer", $con);


  while($row = mysql_fetch_array($result))
  {
    if($row["state"] != "closed"){
      if($row["severity"] == "1-Critical")
          $severity_critical++;
      if($row["severity"] == "2-Major")
          $severity_major++;
      if($row["severity"] == "3-Minor")
          $severity_minor++;

    }
  }

  mysql_close($con);
}

function count_fr_with_filter($filter, $value){
  global $severity_critical;
  global $severity_major;
  global $severity_minor;

  $con = mysql_connect("localhost","root","123456");
  if (!$con)
  {
    die('Could not connect: ' . mysql_error());
  }
  mysql_select_db("fr_db", $con);

  $result = mysql_query("SELECT * FROM FrClass ORDER BY severity,engineer", $con);

  while($row = mysql_fetch_array($result))
  {
    if(($row["state"] == "closed") && !(($filter == "state") && ($value == "closed")))
        continue;

    if($value == "NA")
    {
      if($row["severity"] == "1-Critical")
          $severity_critical++;
      if($row["severity"] == "2-Major")
          $severity_major++;
      if($row["severity"] == "3-Minor")
          $severity_minor++;
    }
    elseif($row[$filter] == $value){
      if($row["severity"] == "1-Critical")
          $severity_critical++;
      if($row["severity"] == "2-Major")
          $severity_major++;
      if($row["severity"] == "3-Minor")
          $severity_minor++;
    }

  }

  mysql_close($con);
}

function show_fr_with_filter($filter, $value){
  $pre_engineer = "";
  $con = mysql_connect("localhost","root","123456");
  if (!$con)
  {
    die('Could not connect: ' . mysql_error());
  }
  mysql_select_db("fr_db", $con);

  $result = mysql_query("SELECT * FROM FrClass ORDER BY severity,engineer", $con);

  echo "<table id='customers'>";

  list_table_head();

  $row_alt = 0;
  while($row = mysql_fetch_array($result))
  {
    if(($row["state"] == "closed") && !(($filter == "state") && ($value == "closed")))
        continue;

    if($value == "NA")
    {
      if($pre_engineer !=  $row["engineer"]){
        $row_alt++;
        $pre_engineer = $row["engineer"];
      }
      list_one_fr($row, $row_alt);
    } 
    elseif($row[$filter] == $value){
      if($pre_engineer !=  $row["engineer"]){
        $row_alt++;
        $pre_engineer = $row["engineer"];
      }
      list_one_fr($row, $row_alt);
    }

  }
  echo "</table>";

  mysql_close($con);
}

//Main Entry
if(isset($_GET["filter"]) && isset($_GET["value"])){
  count_fr_with_filter($_GET["filter"], $_GET["value"]);
  show_fr_with_filter($_GET["filter"], $_GET["value"]);
}
else{
  count_fr();
  show_fr();
}

?>

</body>
</html>

