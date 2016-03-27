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

pre
{
  font-family:"Trebuchet MS";
}

tr.alt 
{
  color:#000000;
  background-color:#EAF2D3;
}

tr.critical
{
  color:#000000;
  background-color:red;
}

tr.tbd
{
  color:#000000;
  background-color:#f5e424;
}

tr.include
{
  color:#000000;
  background-color:lightgreen;
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
//width:100px;
height:75px;
background-color:lightgreen;
border:1px solid black;
border-radius:5px;
box-shadow: 10px 10px 5px #888888;
text-shadow: 5px 5px 5px #FF8000;
}
div#div2
{
width:100px;
background-color:yellow;
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

<script type="text/javascript" src="js/jquery/jquery-1.12.1.min.js"></script>

<script type="text/javascript">
var thirdPartyLink = "https://support.broadcom.com/IMS/Main.aspx?Page=MyCasesDisplay&IssueID=";
var tecLink = "http://cares.web.alcatel-lucent.com/cgi-bin/fast/view.cgi?AR=";

$(function () {
	cnt = 0;
	is_updating = false;
	setInterval(fun,5000);
	function flash(){
		if(cnt++ % 2)
		  	$("#div3").attr("style", "color:red");
		else
			$("#div3").attr("style", "color:blue");
	}

	function fun(){
		$.getJSON('http://135.251.25.50/fr_scan/get_updating_status.php', function (state) {
			if(state.is_updating == "yes"){
			    if(is_updating == false){
					$("#div3").text("Updating is ongoing! " + "FDT Number:" + state.fdt);
					flash_timer = setInterval(flash,1000);
					is_updating = true;
				}
			}
			else{
                if(is_updating == true){
					$("#div3").text("Comments");
					$("#div3").attr("style", "color:black");
					clearInterval(flash_timer);
					is_updating = false;
				}
			}
		})

	}
});

function copy_flow(dst, src){
var i;
dst.className = src.className;
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
	  alert("No items for column:" + lnk.cellIndex);
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

function replaceAll(strOrg,strFind,strReplace){
 var index = 0;
 while(strOrg.indexOf(strFind,index) != -1){
  strOrg = strOrg.replace(strFind,strReplace);
  index = strOrg.indexOf(strFind,index);
 }
 return strOrg
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
}

function updateDart(x,fr)
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
  xmlhttp.open("GET","update_dart.php?fr_id="+fr+"&&texta="+comment,true);
  xmlhttp.send();
}

function update3rdParty(x,fr)
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
  xmlhttp.open("GET","update_fr_3rd_party.php?fr_id="+fr+"&&texta="+comment,true);
  xmlhttp.send();
}

function updateTec(x,fr)
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
  xmlhttp.open("GET","update_fr_tec_link.php?fr_id="+fr+"&&texta="+comment,true);
  xmlhttp.send();
}

function dbclkOnField(x,fr)
{
	var comment = x.firstChild.innerHTML;
	comment = replaceAll(comment, "<br>", "\n");
	x.lastChild.value = comment;
    x.firstChild.style.display="none";
    x.lastChild.style.display="";
}

function onfocusTexa(x,fr)
{
    x.style.background="yellow";
}
function onblurComment(x,fr)
{
	updateFRComment(x, fr);
    x.style.background="white";
	x.style.display="none";
	x.parentElement.firstChild.innerHTML = replaceAll(x.value, "\n", "<br>");	
    x.parentElement.firstChild.style.display="";
}

function onblurTargetDate(x,fr)
{
	updateFRTarget(x, fr);
    x.style.background="white";
	x.style.display="none";
	x.parentElement.firstChild.innerHTML = replaceAll(x.value, "\n", "<br>");	
    x.parentElement.firstChild.style.display="";
}

function onblurThirdParty(x,fr)
{
	update3rdParty(x, fr);

    x.style.background="white";
	x.style.display="none";
	x.parentElement.firstChild.innerHTML = replaceAll(x.value, "\n", "<br>");
	x.parentElement.firstChild.href = thirdPartyLink + x.value;
    x.parentElement.firstChild.style.display="";
}

function onblurTec(x,fr)
{
	updateTec(x, fr);

    x.style.background="white";
	x.style.display="none";
	x.parentElement.firstChild.innerHTML = replaceAll(x.value, "\n", "<br>");
	x.parentElement.firstChild.href = tecLink + x.value;
    x.parentElement.firstChild.style.display="";
}
	
function onblurDart(x,fr)
{
	updateDart(x, fr);
    x.style.background="white";
	x.style.display="none";
	x.parentElement.firstChild.innerHTML = replaceAll(x.value, "\n", "<br>");	
    x.parentElement.firstChild.style.display="";
}

function onUpdateFrList(x, prj, fdt)
{
  var xmlhttp;

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
	   x.disabled='';
	   alert(xmlhttp.responseText);
       //alert("FR list has been updated for release " + prj + "!");
	   location.reload(true);
    }
  }

  xmlhttp.ontimeout=function()
  {
	   x.disabled='';
       alert("Timeout to update FR list for release " + prj + " fdt " + fdt + "!");
	   xmlhttp.abort();
	   location.reload(true);
  }


  xmlhttp.open("GET","upload_file_auto.php"+"?prj="+prj+"&fdt="+fdt,true);
  xmlhttp.timeout = 400000;	
  xmlhttp.send();
  x.disabled='disabled';
}
</script>
</head>

<body>

<?php
$severity_critical = 0;
$severity_major = 0;
$severity_minor = 0;
$fr_count = 0;
$fr_list="http://135.251.206.224/cgi-bin/fr_show?frs=";
$engineer_list;
$project_list;
$thirdPartyLink = "https://support.broadcom.com/IMS/Main.aspx?Page=MyCasesDisplay&IssueID=";
$tecLink = "http://cares.web.alcatel-lucent.com/cgi-bin/fast/view.cgi?AR=";
$fr_link = "http://isam-cq.web.alcatel-lucent.com/cqweb/#/prod/ALU/RECORD/";
$state_filter = "open";
$plan_project = "none";
$dart_filter = "no";
$dip_filter = "no";
$display_rca = "no";
$display_cor = "no";

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
  global $display_rca;
  global $display_cor;

  $total_fr=$severity_critical+$severity_major+$severity_minor;
  $col_id=0;

  echo "<tr>";
  list_table_head_col($col_id, "FR", 0);
  $col_id++;
  list_table_head_col($col_id, "Submitted", 0);
  $col_id++;
  list_table_head_col($col_id, "Submitter", 1);
  $col_id++;	
  list_table_head_col($col_id, "<div id='div2'>" . "<font color=red>" . "Critical:" . $severity_critical . "</font>" . "<br>" . "<font color=DeepPink>" . "Major:" . $severity_major . "</font>" . "<br>" . "<font color=green>" . "Minor:" . $severity_minor . "</font>" . "<br>" . "<font color=black>" . "Total:" . $total_fr . "</font>" . "</div>", 0);
  $col_id++;	
  list_table_head_col($col_id, "St", 1);
  $col_id++;
  list_table_head_col($col_id, "DIP", 0);
  $col_id++;	
  list_table_head_col($col_id, "Brief Description", 0);
  $col_id++;	
  list_table_head_col($col_id, "PlanRel", 1);
  $col_id++;		
  list_table_head_col($col_id, "FDT", 1);
  $col_id++;	
  list_table_head_col($col_id, "3rd Link", 0);
  $col_id++;			
  list_table_head_col($col_id, "TEC Link", 0);
  $col_id++;				
  list_table_head_col($col_id, "IA", 1);
  $col_id++;		
  list_table_head_col($col_id, "Engineer", 1);
  $col_id++;	
  list_table_head_col($col_id, "Target Date", 1);
  $col_id++;	
/* Remove dart 
  list_table_head_col($col_id, "DART", 1);
  $col_id++;		
*/
  list_table_head_col($col_id, "<pre id='div3'>Comments</pre>", 0);
  $col_id++;	
  if($display_cor == "yes"){
    list_table_head_col($col_id, "CodeReview", 0);
    $col_id++;	
    list_table_head_col($col_id, "CloneInfo", 0);
    $col_id++;	
  }
  if($display_rca == "yes"){
    list_table_head_col($col_id, "IntroduceChangeset", 0);
    $col_id++;		
    list_table_head_col($col_id, "RCAContact", 1);
    $col_id++;		
    list_table_head_col($col_id, "DetailedRCAReport", 0);
    $col_id++;
  }	
  echo "</tr>";
}

function list_one_fr($row, $row_alt){
  global $fr_list;
  global $fr_count;
  global $thirdPartyLink;
  global $tecLink;
  global $display_rca;
  global $fr_link;
  global $display_cor;

  $fr_id = $row["fr_id"];
  $fr_comment = $row["comment"];

//  $fr_details = get_fr_details($fr_id);

//  if($row['dart'] == "Critical" || $row['dart'] == "critical" || $row["severity"] == "1-Critical")
  if($row["severity"] == "1-Critical")
      echo "<tr class='critical'>";
//  elseif($row['dart'] == "tbd" || $row['dart'] == "TBD")
  elseif($row["severity"] == "2-Major")
      echo "<tr class='tbd'>";
//  elseif($row['dart'] == "Include" || $row['dart'] == "include")
  elseif($row["severity"] == "3-Minor")	
      echo "<tr class='include'>";		
  elseif($row_alt % 2 == 0)
    echo "<tr>";
  else
    echo "<tr class='alt'>";

  echo "<td><a href=\"" . $fr_link . $row['fr_id'] . "\">" . $row['fr_id'] . "</td>";
  echo "<td>" . $row['sub_date'] . "</td>";
  echo "<td>" . $row['submitter'] . "</td>";	
  echo "<td>" . $row['severity'] . "</td>";
  echo "<td>" . $row['state'] . "</td>";
  echo "<td>" . $row['diph'] . "</td>";
  echo "<td>" . $row['brief_des'] . "</td>";
  echo "<td>" . $row['plan_rel'] . "</td>";
  echo "<td>" . $row['fdt'] . "</td>";	
  echo "<td ondblclick=\"dbclkOnField(this,'$fr_id');\"><a href=\"" . $thirdPartyLink . $row['3rd_party'] . "\">" . $row['3rd_party'] . "</a>" . "<textarea rows='1' cols='16' name='texta' style='display:none;' onfocus=\"onfocusTexa(this,'$fr_id');\" onblur=\"onblurThirdParty(this,'$fr_id');\">" . $row['3rd_party']  . "</textarea>" . "</td>";	   
  echo "<td ondblclick=\"dbclkOnField(this,'$fr_id');\"><a href=\"" . $tecLink . $row['tec_ticket'] . "\">" . $row['tec_ticket'] . "</a>" . "<textarea rows='1' cols='16' name='texta' style='display:none;' onfocus=\"onfocusTexa(this,'$fr_id');\" onblur=\"onblurTec(this,'$fr_id');\">" . $row['tec_ticket']  . "</textarea>" . "</td>";
  echo "<td>" . $row['ia'] . "</td>";
  echo "<td>" . $row['engineer'] . "</td>";	
  echo "<td onclick=\"dbclkOnField(this,'$fr_id');\"><span>" . $row['target_date'] . "</span>" . "<textarea rows='1' cols='16' name='texta' style='display:none;' onfocus=\"onfocusTexa(this,'$fr_id');\" onblur=\"onblurTargetDate(this,'$fr_id');\">" . $row['target_date']  . "</textarea>" . "</td>";
//  echo "<td ondblclick=\"dbclkOnField(this,'$fr_id');\"><span>" . $row['dart'] . "</span>" . "<textarea rows='1' cols='16' name='texta' style='display:none;' onfocus=\"onfocusTexa(this,'$fr_id');\" onblur=\"onblurDart(this,'$fr_id');\">" . $row['dart']  . "</textarea>" . "</td>";
//  echo "<td>" . $row['dart'] . "</td>" ;
  echo "<td onclick=\"dbclkOnField(this,'$fr_id');\"><span>" . $row['comment'] . "</span>" . "<textarea rows='8' cols='64' name='texta' style='display:none;' onfocus=\"onfocusTexa(this,'$fr_id');\" onblur=\"onblurComment(this,'$fr_id');\">" . $row['comment']  . "</textarea>" . "</td>";

  if($display_cor == "yes"){
    echo "<td>" .  $row['cor'] . "</td>";
    echo "<td>" .  $row['clone_info'] . "</td>";
  }

  if($display_rca == "yes"){
    echo "<td>" .  $row['changeset'] . "</td>";
    echo "<td>" .  $row['rca_con'] . "</td>";
    echo "<td>" . $row['rca_rept'] . "</td>";
  }
  echo "</tr>";
  if($fr_count == 0)
      $fr_list = $fr_list . $row['fr_id'];
  else
      $fr_list = $fr_list . "," . $row['fr_id'];

  $fr_count++;	

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

  echo "<table id='customers' border='1px solid #98bf21'>"; 

  list_table_head();

  $row_alt = 0;
  while($row = mysql_fetch_array($result))
  {
    if(($row["state"] != "closed")
	   &&($row["state"] != "Duplicate")	   
	   &&($row["state"] != "Verified")
	   &&($row["state"] != "Resolved")
	   &&($row["state"] != "Delivered")
	   &&($row["state"] != "Rejected")
	   &&($row["state"] != "Unplanned")){
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
    if(($row["state"] != "closed")
	   &&($row["state"] != "Duplicate")
	   &&($row["state"] != "Verified")
	   &&($row["state"] != "Resolved")
	   &&($row["state"] != "Delivered")
	   &&($row["state"] != "Rejected")
	   &&($row["state"] != "Unplanned")){
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

function count_fr_with_filter($rel, $state, $dart, $fdt, $dip){
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
	 //Release filter
	 if(($rel != "none") && ($rel != $row["plan_rel"]))
		 continue;

    //State filter
    if($state == "open") {
		if($row["state"] == "Verified")
		 continue;
	   elseif($row["state"] == "Resolved")
		 continue;
	   elseif($row["state"] == "Delivered")
		 continue;
      elseif($row["state"] == "Build")
		 continue;
      elseif($row["state"] == "Rejected")
		 continue;		
      elseif($row["state"] == "Unplanned")
		 continue;	
      elseif($row["state"] == "closed")
		 continue;
	   elseif($row["state"] == "Duplicate")
		 continue;		
	 }
	 elseif($state == "all") {
	 }	  
    elseif($row["state"] != $state)
        continue;
    
	//dart filter
    if($dart == "yes") {
		if(($row["dart"] != "Critical") && ($row["dart"] != "Include") && ($row["dart"] != "TBD"))
		 continue;
	 }
	 elseif($dart == "no") {
	 }	  
    elseif($row["dart"] != $dart)
        continue;

	//fdt filter
    if($fdt == "domain") {
		if(($row["fdt"] != "1252") && ($row["fdt"] != "1251") && ($row["fdt"] != "1356") && ($row["fdt"] != "1343") && ($row["fdt"] != "BCMBL"))
		 continue;
		if(($row["fdt"] == "BCMBL") && ($row["ia"] != "dingjun he"))
		 continue;
	 }
	 elseif($fdt == "all") {
	 }	  
    elseif($row["fdt"] != $fdt)
        continue;

	//dip filter
    if(($dip != "no") && ($dip != $row["diph"])) {
		 continue;
	 }

	//Counters
    if($row["severity"] == "1-Critical")
        $severity_critical++;
    if($row["severity"] == "2-Major")
        $severity_major++;
    if($row["severity"] == "3-Minor")
        $severity_minor++;

  }

  mysql_close($con);
}

function show_fr_with_filter($rel, $state, $dart, $fdt, $dip){
  $con = mysql_connect("localhost","root","123456");
  if (!$con)
  {
    die('Could not connect: ' . mysql_error());
  }
  mysql_select_db("fr_db", $con);

  $result = mysql_query("SELECT * FROM FrClass ORDER BY severity,engineer", $con);

  echo "<table id='customers' border='1px solid #98bf21'>";

  list_table_head();

  $row_alt = 0;
  while($row = mysql_fetch_array($result))
  {
	 if(($rel != "none") && ($rel != $row["plan_rel"]))
		 continue;

    if($state == "open") {
		if($row["state"] == "Verified")
		 continue;
	   elseif($row["state"] == "Resolved")
		 continue;
	   elseif($row["state"] == "Delivered")
		 continue;
      elseif($row["state"] == "Build")
		 continue;
      elseif($row["state"] == "Rejected")
		 continue;		
      elseif($row["state"] == "Unplanned")
		 continue;	
      elseif($row["state"] == "closed")
		 continue;	
	   elseif($row["state"] == "Duplicate")
		 continue;				
	 }
	 elseif($state == "all") {
	 }
    elseif($row["state"] != $state)
        continue;

    if($dart == "yes") {
		if(($row["dart"] != "Critical") && ($row["dart"] != "Include") && ($row["dart"] != "TBD"))
		 continue;
	 }
	 elseif($dart == "no") {
	 }	  
    elseif($row["dart"] != $dart)
        continue;

	//fdt filter
    if($fdt == "domain") {
		if(($row["fdt"] != "1252") && ($row["fdt"] != "1251") && ($row["fdt"] != "1356") && ($row["fdt"] != "1343") && ($row["fdt"] != "BCMBL"))
		 continue;
		if(($row["fdt"] == "BCMBL") && ($row["ia"] != "dingjun he"))
		 continue;		
	 }
	 elseif($fdt == "all") {
	 }	  
    elseif($row["fdt"] != $fdt)
        continue;

	//dip filter
    if(($dip != "no") && ($dip != $row["diph"])) {
		 continue;
	 }

    $row_alt++;

    list_one_fr($row, $row_alt);

  }
  echo "</table>";

  mysql_close($con);
}


function get_value($field){
  $value = "";

  if($field->DataType == "SHORT_STRING")
	  $value = $field->CurrentValue;

  if($field->DataType == "MULTILINE_STRING"){
      $arrlength = count($field->CurrentValue);
	  $value = "";
      for($x=0;$x<$arrlength;$x++) {
          $value = $value . "<br>". $field->CurrentValue[$x];
      }
  }
  return $value;
}

function get_field($fields, $field_name){
  $arrlength = count($fields);
  for($x=0;$x<$arrlength;$x++) {
    if($fields[$x]->FieldName == $field_name)
        return $fields[$x];
  }
  return "";
}

function get_fr_field($fr_detail, $field_name){
  $field = get_field($fr_detail->fields, $field_name);
  if($field == "")
	  return "";
  return get_value($field);
}

function get_fr_details($fr_id){
  $cookie_jar = dirname(__FILE__)."/pic.cookie";
  $url_fr = "http://isam-cq.web.alcatel-lucent.com/cqweb/cqartifactdetails.cq?action=GetCQRecordDetails&resourceId=cq.record%3AFR_IR%2F" . $fr_id . "%40prod%2FALU&state=VIEW&tabIndex=0&acceptAllTabsData=true&cquid=rwQJ7z0QuIkUhFL2QDYzHeC";

  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, $url_fr);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
  $output = curl_exec($ch);
  curl_close($ch);

  $output_json = json_decode($output);
  return $output_json;
}



function get_dart($fr_detail, $prj){
  $plm_assess = get_field($fr_detail->fields, "PLMassessment");

  $arrlength = count($plm_assess);
  $value = "";
  for($x=0;$x<$arrlength;$x++) {
      $value = $plm_assess->CurrentValue[$x];
	  $value = trim($value);
      if(strpos($value,"DART_" . $prj)){
		  $value_array = explode(":", $value);
		  return $value_array[1];
	  }
  }

  return "TBD";
}

function update_fr_cq($fr_id, $comment){
  $cookie_jar = dirname(__FILE__)."/pic.cookie";
  $url = "http://isam-cq.web.alcatel-lucent.com/cqweb/cqartifactdetails.cq";
  $post_data = array ("action" => "RecordCommit",
					  "Fields" => "%5B%7B%22FieldName%22%3A%20%22Note_Entry%22%2C%20%22FieldValue%22%3A%20%5B%22Cov%22%5D%7D%5D",
					  "state" => "MODIFY", 
					  "resourceId" => "cq.record%3AFR_IR%2F" . $fr_id . "%40prod%2FALU", 
					  "cquid" => "rwQJ7z0QuIkUhFL2QDYzHeC");

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
  curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);

  $output = curl_exec($ch);
  curl_close($ch);
//  echo json_encode(array(array("FieldName" => "Note_Entry", "FieldValue" => array("a","b","c"))));
	echo "<br>";
	echo http_build_query(array(array("FieldName" => "Note_Entry",
									   "FieldValue" => array("HDJ"))));
	echo "<br>";	
  echo $output;
}

//Main Entry
//Filter options
if(isset($_GET["state"]))
	$state_filter = $_GET["state"];
else
   $state_filter = "open";

if(isset($_GET["plan_rel"]))
	$plan_project = $_GET["plan_rel"];
else
   $plan_project = "none";

if(isset($_GET["dart"]))
	$dart_filter = $_GET["dart"];
else
   $dart_filter = "no";

if(isset($_GET["fdt"]))
	$fdt_filter = $_GET["fdt"];
else
   $fdt_filter = "1252";

if(isset($_GET["dip"]))
	$dip_filter = $_GET["dip"];
else
   $dip_filter = "no";

//Display options
if(isset($_GET["rca"]))
	$display_rca = $_GET["rca"];
else
   $display_rca = "no";

if(isset($_GET["cor"]))
	$display_cor = $_GET["cor"];
else
   $display_cor = "no";



count_fr_with_filter( $plan_project, $state_filter, $dart_filter, $fdt_filter, $dip_filter);
show_fr_with_filter( $plan_project, $state_filter, $dart_filter, $fdt_filter, $dip_filter);

if(isset($_GET["update"]))
	echo "<input type='button' value='Update Team FR List' onclick=\"onUpdateFrList(this, '$plan_project', '$fdt_filter');\">";

?>

</body>
</html>

