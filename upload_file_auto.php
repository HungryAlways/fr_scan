<?php
// example of how to use basic selector to retrieve HTML contents
include('../simplehtmldom/simple_html_dom.php');

//$web_side_lnk = "http://devws165.be.alcatel-lucent.com:8889/query/";
$web_side_lnk = "http://aww.sh.bel.alcatel.be/metrics/datawarehouse/query/";
$fdt_filter = "1252";
$log_file = "update_fr_log.htm";
$lock_tag_file = 'update_fr_blog_flag.lock';
$passwd = "Hdjmh$1202";

function log_update($trc){
  global $log_file;
  if(!file_exists($log_file )){
	  touch($log_file);
  }
  $time = get_time();
  file_put_contents($log_file, "$time : " . $trc . "<br>\n", FILE_APPEND);
}

function lock_for_update($fdt){
  global $lock_tag_file;
  global $log_file;
  if(file_exists($lock_tag_file )){
    log_update("updating is ongoing, exit!!");
	echo "There is update ongoing, try later!";
    exit();
  }

  touch($lock_tag_file);
  file_put_contents($lock_tag_file, "$fdt", FILE_APPEND);
}

function unlock_for_update(){
  global $lock_tag_file;
  if(file_exists($lock_tag_file )){
    unlink($lock_tag_file);
  }
}

function set_fr_state_to_close($prj, $fdt){
  $con = mysql_connect("localhost","root","123456");
  if (!$con)
  {
    die('Could not connect: ' . mysql_error());
  }
  mysql_select_db("fr_db", $con);

  if($prj == "none")
    mysql_query("UPDATE FrClass SET state = 'closed' "  . "WHERE fdt = " . "'" . "$fdt" . "'" , $con);
  else
    mysql_query("UPDATE FrClass SET state = 'closed' " . "WHERE plan_rel = " . "'". "$prj" . "'" . " AND fdt = "  . "'" . "$fdt" . "'" , $con);

  mysql_close($con);
  return "OK";
}

function insert_fr_to_db($fr_info){
  $fr_id = $fr_info[0];
  $sub_date = $fr_info[1];
  $severity = $fr_info[2];
  $state = $fr_info[3];
  $description = $fr_info[4];
  $fdt = $fr_info[5];
  $ia = $fr_info[6];
  $submitter = $fr_info[7];
  $orig_proj = $fr_info[8]; 
  $plan_rel = $fr_info[9]; 
  $diph = $fr_info[10]; 
  $ec  = $fr_info[11]; 
  $domain = $fr_info[12]; 
  $engineer = $fr_info[13]; 

  $con = mysql_connect("localhost","root","123456");
  if (!$con)
  {
    die('Could not connect: ' . mysql_error());
  }

  $fr_details = get_fr_details($fr_id);
  $cor_no_cor_reason = str_replace("\r","<br>",get_fr_field($fr_details, "ReasonNoCodeReview"));
  $cor_flag =  get_fr_field($fr_details, "ConductCodeReview");
  $cor_type =  get_fr_field($fr_details, "TypeOfCodeReview");
  $cor_rev_board_id =  str_replace("\r","<br>",get_fr_field($fr_details, "ReviewBoardID"));
  $cor_peer_review_by =  str_replace("\r","<br>",get_fr_field($fr_details, "PeerReviewPairProgramBy"));
  $rcr =  str_replace("\r","<br>",get_fr_field($fr_details, "RCRWithBug"));
  $reason =  str_replace("\r","<br>",get_fr_field($fr_details, "ReasonForProblem"));

  if($cor_flag == "Y"){
	  $cor = $cor_flag . ":" . $cor_type;	  
	  if($cor_type == "Review Board Tool")
        $cor = $cor . "<br>" . "Review Board Id: " .  $cor_rev_board_id;
	  if(($cor_type == "Peer Review") ||
		($cor_type == "Pair Programming") || 
		($cor_type == "Peer Review & Pair Programming"))
        $cor = $cor . "<br>" . "Reviewed by: " . "<br>" .  $cor_peer_review_by;
  }
  else{
      $cor = $cor_flag . ":" . $cor_no_cor_reason;
  }

  $rca = str_replace("\r","<br>",get_fr_field($fr_details, "DetailedRCAReport"));
  $clone_info = str_replace("\r","<br>",get_fr_field($fr_details, "CloneInfo"));
  $change_set = str_replace("\r","<br>",get_fr_field($fr_details, "ChangeSet"));
  $rca_contact = str_replace("\r","<br>",get_fr_field($fr_details, "RCAContact"));
  $dart = str_replace("\r","",get_dart($fr_details, "$plan_rel"));


  mysql_select_db("fr_db", $con);

  $fr_description = htmlentities($description, ENT_QUOTES);
  if(strlen($engineer) <= 1)
    $fr_engineer = "";
  else
    $fr_engineer = $engineer;
  
  $result = mysql_query("SELECT fr_id FROM FrClass WHERE fr_id = '$fr_id'", $con);
  if(!mysql_fetch_array($result)){ 
     mysql_query("INSERT INTO FrClass (fr_id, sub_date, severity, state, brief_des, fdt, ia, orig_proj, plan_rel, diph, ec, domain, engineer, dart, cor, rca_rept, changeset, rca_con, clone_info, submitter, reason, rcr) VALUES('$fr_id', '$sub_date','$severity','$state','$fr_description','$fdt','$ia','$orig_proj','$plan_rel','$diph','$ec','$domain','$fr_engineer', '$dart', '$cor', '$rca', '$change_set', '$rca_contact', '$clone_info', '$submitter', '$reason', '$rcr')", $con); 
  }
  else {
    mysql_query("UPDATE FrClass SET sub_date = '$sub_date',severity = '$severity',state = '$state',brief_des = '$fr_description',fdt = '$fdt',ia = '$ia',orig_proj = '$orig_proj',plan_rel = '$plan_rel',diph = '$diph',ec = '$ec',ec = '$domain',engineer = '$fr_engineer',dart = '$dart',cor = '$cor',rca_rept = '$rca',changeset = '$change_set',rca_con = '$rca_contact',clone_info = '$clone_info',submitter = '$submitter',reason = '$reason',rcr = '$rcr' WHERE fr_id = '$fr_id'", $con);
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
  echo "Total $line_num FR was added or updated!\n";
  fclose($file);
}

function parse_fr_html_table($html, $prj){
  $line_num = 0;
  $fr_array = array();
  $col = 0;

  foreach($html->find('tr') as $element) {
	if($line_num == 0){
		$line_num++;
		continue;
	}

	$col = 0;
    foreach($element->find('td') as $col_item){
        if($col == 0)
             $fr_array[$col] = $col_item->firstChild()->firstChild()->innertext;
		else
             $fr_array[$col] = $col_item->firstChild()->innertext;
		$col++;
	}

	insert_fr_to_db($fr_array); 

	$line_num++;
  }
  $line_num--;
  echo "Total $line_num FR was added or updated for $prj!\n";
}

function update_frs_for_one_project($prj){
  global $web_side_lnk;
  global $fdt_filter;

  $fr_number = 0;
  $html_link = $web_side_lnk . "FRStatus.cgi?changeFormDB=&project=" . "$prj" . "&origproj=&team=" . "$fdt_filter" . "&tp=&states=NXQHLBRVJUD&per=FDT&from=&till=&site=&phase=&haby=&nothaby=&action=Apply";

  if($prj == "none")
     $html_link = $web_side_lnk . "FRStatus.cgi?changeFormDB=&project=&origproj=&team=" . "$fdt_filter" . "&tp=&states=NXQHLBRVJUD&per=FDT&from=&till=&site=&phase=&haby=&nothaby=&action=Apply";

  // get DOM from URL or file
  $html_doc = file_get_html($html_link);

  foreach($html_doc->find('th') as $element) {
	if($element->firstChild()->innertext == "Total"){
		  $html_link = $web_side_lnk . $element->parent()->childNodes(7)->firstChild()->href;
	}
  }

  //echo "$html_link";
  $html_tmp =  file_get_html($html_link);
  //echo "<table>";
  foreach($html_tmp->find('tr') as $element1){
  $test = $element1->innertext;
  $fr_number++;
  //echo "<tr>$test</tr>";
  }
 // echo "</table>";
 // echo "Total FR Number:" + $fr_number;
  if($html_tmp){
    parse_fr_html_table($html_tmp, $prj);
  }
}
function get_rel_list_for_fdt($fdt){
  global $web_side_lnk;
  $rel_list = array();
  $x = 0;

  $html_link = $web_side_lnk . "FRStatus.cgi?changeFormDB=&project=&origproj=&team=" . "$fdt" . "&tp=&states=NXQH&per=FDT&from=&till=&site=&phase=&haby=&nothaby=&action=Apply";

  // get DOM from URL or file
  $html_doc = file_get_html($html_link);

  foreach($html_doc->find('th') as $element) {
	if($element->firstChild()->innertext == "$fdt"){
		$prj = $element->parent()->childNodes(3)->innertext;
        $rel_list[$x++] = $prj;
	}
  }
  return $rel_list;
}
function get_time(){
	$time = time();
	return date("y-m-d h:i:s",$time);
}
function update_frs_for_all_project(){
  global $fdt_filter;
  global $log_file;

  $rel_list = get_rel_list_for_fdt($fdt_filter);
  for($x = 0; $x < count($rel_list); $x++){
	  log_update("update_frs_for_all_project: start to update fr for release '$rel_list[$x]' of fdt '$fdt_filter'");
	  set_fr_state_to_close($rel_list[$x], $fdt_filter );
      update_frs_for_one_project($rel_list[$x]);
	  log_update("update_frs_for_all_project: end update fr for release '$rel_list[$x]' of fdt '$fdt_filter'");
  }
}

function login_cq(){
  global $passwd;
  $cookie_jar = dirname(__FILE__)."/pic.cookie";
  $url = "http://isam-cq.web.alcatel-lucent.com/cqweb/cqlogin.cq";
  $post_data = array ("action" => "DoLogin", "loginId" => "dingjun he","password" => $passwd, "repository" => "prod", "tzOffset" => "GMT+8:00", "loadAllRequiredInfo" => "true", "userDb" => "ALU", "cquid" => "rwQJ7z0QuIkUhFL2QDYzHeC");

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
  curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
  curl_exec($ch);
  curl_close($ch);
}

function get_value($field){
  $value = "";

  if($field->DataType == "SHORT_STRING")
	  $value = $field->CurrentValue;

  if($field->DataType == "MULTILINE_STRING"){
      $arrlength = count($field->CurrentValue);
	  $value = "";
      for($x=0;$x<$arrlength;$x++) {
          $value = $value . str_replace("\r","", $field->CurrentValue[$x]) . "\r";
      }
  }

  if($field->DataType == "RESOURCE_LIST"){
      $arrlength = count($field->CurrentValue);
	  $value = "";
      for($x=0;$x<$arrlength;$x++) {
          $value = $value . str_replace("\r","", $field->CurrentValue[$x]->DisplayName) . "\r";
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

  $arrlength = count($plm_assess->CurrentValue);
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

set_time_limit(0);
ignore_user_abort();

if(isset($_GET["fdt"]) && ($_GET["fdt"] != "domain")){
    $fdt_filter = $_GET["fdt"];
}

lock_for_update($fdt_filter);

log_update("Enter into upload_file_auto.php");

login_cq();

if(isset($_GET["prj"]) && ($_GET["prj"] != "none")) {
  log_update("set_fr_state_to_close");
  set_fr_state_to_close($_GET["prj"], $fdt_filter );
  log_update("update_frs_for_one_project");
  update_frs_for_one_project($_GET["prj"]);
}
else {
	log_update("Start to update_frs_for_all_project");
	update_frs_for_all_project();
	log_update("End to update_frs_for_all_project");

}

log_update("Exit from upload_file_auto.php<br>");

unlock_for_update();

?>
