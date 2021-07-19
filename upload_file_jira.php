<?php
// example of how to use basic selector to retrieve HTML contents
include('../simplehtmldom/simple_html_dom.php');

$web_side_lnk = "https://jiradc2.ext.net.nokia.com/rest/api/latest/search?jql=issueType%20in%20%28Bug%29%20AND%20project%20in%20%28%22BBDHB%22%29%20AND%20%22Work%20Team%22%20in%20%28%22SH:%20DH-WLan%22%29&fields=key,issuetype,status,summary,reporter,assignee,assignee,resolution,customfield_11890,versions,fixVersions,customfield_14545,customfield_37722,created,custom,customfield_37100,customfield_27892&startAt=0&maxResults=1000";
//$web_side_lnk_pre = "https://jiradc2.ext.net.nokia.com/rest/api/latest/search?jql=issueType%20in%20%28Bug%29%20AND%20project%20in%20%28%22BBDHB%22%29%20AND%20%22Work%20Team%22%20in%20%28%22";
$web_side_lnk_pre = "https://jiradc2.ext.net.nokia.com/rest/api/latest/search?jql=issueType%20in%20%28Bug";
$web_side_lnk_prod = "%29%20AND%20project%20in%20%28%22"; // suffix with prod
$web_side_lnk_fdt_bbdhb = "%22%29%20AND%20%22Work%20Team%22%20in%20%28%22"; // suffix with fdt
$web_side_lnk_fdt_bbdprod = "%22%29%20AND%20%22cf[14545]%22%20in%20%28%22";
$web_side_lnk_suf_fdt = "%22%29";
$web_side_lnk_pre_prj = "%20AND%20fixVersion%20in%20%28%22";
$web_side_lnk_suf_prj = "%22%29";
$web_side_lnk_suf_open = "%20AND%20status%20in%20%28New,%20Accepted,%20Hold,%20Query%29";
$web_side_lnk_last = "&fields=key,issuetype,status,summary,reporter,assignee,assignee,resolution,customfield_11890,versions,fixVersions,customfield_14545,customfield_37722,created,custom,customfield_37100,customfield_32017,customfield_27892,customfield_19503,customfield_37061,issuelinks&startAt=0&maxResults=1000";

$fdt_filter = "1252";
$log_file = "update_fr_log.htm";
$last_update_time_file = "last_update_time.log";
$lock_tag_file = 'update_fr_blog_flag.lock';
$passwd = "Hdjmh@1202";
$is_log = true;
$is_skip_updating_for_same_state = true;
$state_open_list =  array("New", "Accepted", "Query", "Hold");
$mysql_con = NULL;
$proxy_ip = "135.251.33.31";
$proxy_port = 8080;
$nwf_fdt_list=array("1261", "1530", "1545");
$nwf_plan_rel_prefix="NWF";
$force="no";
$prj_filter="none";
$prod = "BBDHB";

function is_nwf_fdt($fdt){
	global $nwf_fdt_list;
    if(in_array($fdt, $nwf_fdt_list)){
		return true;
	}
	return false;
}
function is_nwf_prj($prj){
	global $nwf_plan_rel_prefix;
    if(!strncmp($nwf_plan_rel_prefix, $prj, 3)){
		return true;
	}
	return false;
}

function get_week_number(){
    return (idate('y')*100 + idate('W'));
}

function get_last_week_number(){
    return (idate('y', time() - (7 * 24 * 60 * 60))*100 + idate('W', time() - (7 * 24 * 60 * 60)));
}

function get_week_day(){
    return (idate('w') == 0)?7:idate('w');
}

function get_burn_down_stats($week, $day, $fdt, $rel){
  global $mysql_con;
  $last_day = ($day == 1)?7:($day - 1);
  $last_week = ($day == 1)?get_last_week_number():get_week_number();
  $yesterday_burn_list = array();
  
  log_update("get_burn_down_stats for: " . $week . $fdt . $rel);
  
  //Copy yesterday's result for first time
  $result = mysqli_query($mysql_con, "SELECT * FROM BurnDownStats " . "WHERE week = " . "'". "$last_week" . "'" . " AND fdt = "  . "'" . "$fdt" . "'"  . " AND wkday = "  . "'" . "$last_day" . "'");
  while($row = mysqli_fetch_assoc($result))
  {
	log_update("get_burn_down_stats yesterday's record: !" . $row['fdt'] . $row['week'] . $row['wkday'] . $row['rel']);
    array_push($yesterday_burn_list, $row); 
  }  
  mysqli_free_result($result);
  foreach($yesterday_burn_list as $list) {
      log_update("get_burn_down_stats yesterday's record again: !" . $list['fdt'] . $list['week'] . $list['wkday'] . $list['rel']);
      $rel_tmp = $list['rel'];
      $result = mysqli_query($mysql_con, "SELECT * FROM BurnDownStats " . "WHERE week = " . "'". "$week" . "'" . " AND fdt = "  . "'" . "$fdt" . "'"  . " AND rel = "  . "'" . "$rel_tmp" . "'"  . " AND wkday = "  . "'" . "$day" . "'");
      if($row = mysqli_fetch_assoc($result))
      {
	      log_update("get_burn_down_stats skip existing record for rel:" . $rel_tmp);
        mysqli_free_result($result);
        continue;
      }   
      mysqli_free_result($result);
      log_update("get_burn_down_stats copy yesterday record for rel:" . $rel_tmp);
      $open_cnt = $list['open'];
      $close_cnt = $list['closed'];    
      mysqli_query($mysql_con, "INSERT INTO BurnDownStats (week, wkday, fdt, rel, open, closed) VALUES('$week', '$day', '$fdt','$rel_tmp','$open_cnt', '$close_cnt')");      
  }

  $result = mysqli_query($mysql_con, "SELECT * FROM BurnDownStats " . "WHERE week = " . "'". "$week" . "'" . " AND fdt = "  . "'" . "$fdt" . "'"  . " AND rel = "  . "'" . "$rel" . "'"  . " AND wkday = "  . "'" . "$day" . "'");

  while($row = mysqli_fetch_assoc($result))
  {
	  log_update("get_burn_down_stats successful!");
    mysqli_free_result($result);
    return $row;
  }
  log_update("get_burn_down_stats, create new record!");
  $default_stats['week'] = $week;
  $default_stats['fdt'] = $fdt;
  $default_stats['wkday'] = $day;
  $default_stats['rel'] = $rel;
  $default_stats['imcoming'] = 0;
  $default_stats['handled'] = 0;
  $default_stats['open'] = 0;
  $default_stats['open_critical'] = 0;
  $default_stats['open_major'] = 0;
  $default_stats['open_minor'] = 0;
  $default_stats['closed'] = 0;
  
  mysqli_free_result($result);
  mysqli_query($mysql_con, "INSERT INTO BurnDownStats (week, wkday, fdt, rel, incoming, handled, open) VALUES('$week', '$day', '$fdt','$rel',0, 0, 0)"); 
  

  return $default_stats;
}

function update_burn_down_stats($week, $day, $fdt, $rel, $stats){
  global $mysql_con;
  log_update("update_burn_down_stats for: " . $week . $fdt . $rel);

  $incoming = $stats['incoming'];
  $handled = $stats['handled'];
  $open = $stats['open'];
  $open_critical = $stats['open_critical'];
  $open_major = $stats['open_major'];
  $open_minor = $stats['open_minor'];
  $closed = $stats['closed'];

  log_update("UPDATE BurnDownStats SET incoming = '$incoming',handled = '$handled',open = '$open',open_critical = '$open_critical',open_major = '$open_major',open_minor = '$open_minor',closed = '$closed' WHERE week = '$week' AND fdt = '$fdt' AND rel = '$rel'  AND wkday = '$day'");
  mysqli_query($mysql_con, "UPDATE BurnDownStats SET incoming = '$incoming',handled = '$handled',open = '$open',open_critical = '$open_critical',open_major = '$open_major',open_minor = '$open_minor',closed = '$closed' WHERE week = '$week' AND fdt = '$fdt' AND rel = '$rel'  AND wkday = '$day'");

}

function update_last_time(){
    global $last_update_time_file;
    if(file_exists($last_update_time_file )){
      unlink($last_update_time_file);
    }    
    touch($last_update_time_file);
    
    $time = get_time();
    file_put_contents($last_update_time_file, "$time", FILE_APPEND);
}
function log_update($trc, $bg_color = "white"){
  global $log_file;
  global $is_log;

  if(!$is_log)
	  return;
  if(!file_exists($log_file )){
	  touch($log_file);
  }
  if(filesize($log_file) > 2*1024*1024){
	  unlink($log_file);
	  touch($log_file);
  }
  $time = get_time();
  file_put_contents($log_file, "$time : " . "<span style='background:$bg_color;'>"  . $trc . "</span>" . "<br>\n", FILE_APPEND);
}

function lock_for_update($fdt){
  global $lock_tag_file;
  global $log_file;
  if(file_exists($lock_tag_file )){
    log_update("updating is ongoing, exit!!");
    unlink($lock_tag_file);
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

function set_fr_state_to_close($prj, $fdt, $fr_list_in_web){
  global $fdt_filter;
  global $mysql_con;
  $week = get_week_number();
  $wkday = get_week_day();

  log_update("set_fr_state_to_close for prj:" . $prj . ", fdt:" . $fdt);

  if($prj == "none")
    $result = mysqli_query($mysql_con, "SELECT * FROM FrClass "  . "WHERE fdt = " . "'" . "$fdt" . "'" );
  else
    $result = mysqli_query($mysql_con, "SELECT * FROM FrClass " . "WHERE plan_rel = " . "'". "$prj" . "'" . " AND fdt = "  . "'" . "$fdt" . "'"  );

  while($row = mysqli_fetch_array($result))
  {
	  //log_update("Checking " . $row["fr_id"] . " in web list or not!");
    if(!in_array($row["fr_id"], $fr_list_in_web)){
		    $fr_id = $row["fr_id"];
		    mysqli_query( $mysql_con, "DELETE FROM FrClass "  . "WHERE fr_id = " . "'" . "$fr_id" . "'");
		    log_update($fr_id . " is not in web list, will close it!");
    }
  }

  return "OK";
}

function is_skipped($state, $engineer, $sev, $row, $fdt, $plan_rel, $ec, $ont_type, $business_line){
  log_update("is_skipped: " . $row["state"] . "=" . $state  . "? " . $row["engineer"] . "=" . $engineer . "? " . $row["severity"] . "=" . $sev . "? " . $row["fdt"] . "=" . $fdt . "? " . $row["plan_rel"] . "=" . $plan_rel . "? " . $row["ont_type"] . "=" . $ont_type . "? ");
	//if(($state == $row["state"]) && ($engineer == $row["engineer"]) && ($sev == $row["severity"])  && ($fdt == $row["fdt"])  && ($plan_rel == $row["plan_rel"]) && ($row["ont_type"] != $ont_type)  && ($row["ec"] == $ec))
  if(($state == $row["state"]) && ($engineer == $row["engineer"]) && ($sev == $row["severity"])  && ($fdt == $row["fdt"])  && ($plan_rel == $row["plan_rel"])  && ($row["ont_type"] == $ont_type)  && ($row["business_line"] == $business_line))
		return true;
	return false;
}

function insert_fr_to_db($fr_info){
  $fr_id = $fr_info[0];
  $sub_date = $fr_info[1];
  $priority = $fr_info[2];
  $severity = $fr_info[3];
  $state = $fr_info[4];
  $description = $fr_info[5];
  $fdt = $fr_info[6];
  $ia = $fr_info[7];
  $submitter = $fr_info[8];
  $orig_proj = $fr_info[9]; 
  $plan_rel = $fr_info[10]; 
  $diph = $fr_info[11]; 
  $ec  = $fr_info[12]; 
  $domain = $fr_info[13]; 
  $engineer = $fr_info[14]; 
  $ont_type = $fr_info[15]; 
  $business_line = $fr_info[16]; 
  $rcr = "";
  global $is_skip_updating_for_same_state;
  global $state_open_list;
  global $fdt_filter;
  global $mysql_con;
  $week = get_week_number();
  $wkday = get_week_day();
  $is_fr_exist = false;
  global $force;

  if($plan_rel == "")
      $plan_rel = $orig_proj;
      
  //check whether it is needed for update
  log_update("Updating FR : " . $fr_id . ",new state is : " . $state);
  if($is_skip_updating_for_same_state){
	  $result = mysqli_query($mysql_con, "SELECT * FROM FrClass WHERE fr_id = '$fr_id'");
	  $row = mysqli_fetch_array($result);
     
    if($row)
        $is_fr_exist = true;
	  
    log_update("Updating FR : " . $fr_id . ",current state is : " . $row["state"]);
	  if($row && is_skipped($state, $engineer, $severity, $row, $fdt, $plan_rel, $ec, $ont_type, $business_line) && ($force == "no")){ 
		    log_update("Skip FR : " . $fr_id);
		    return;
	  }
  }

  $fr_description = htmlentities($description, ENT_QUOTES);
  if(strlen($engineer) <= 1)
    $fr_engineer = "";
  else
    $fr_engineer = $engineer;

   if(!$is_fr_exist){
	     log_update("INSERT INTO FrClass (fr_id, sub_date, severity, state, brief_des, fdt, ia, orig_proj, plan_rel, diph, ec, domain, engineer, dart, cor, rca_rept, changeset, rca_con, clone_info, submitter, reason, rcr, ont_type) VALUES('$fr_id', '$sub_date','$severity','$state','$fr_description','$fdt','$ia','$orig_proj','$plan_rel','$diph','$ec','$domain','$fr_engineer', '$dart', '$cor', '$rca', '$change_set', '$rca_contact', '$clone_info', '$submitter', '$reason', '$rcr', '$ont_type')", "red");
     if(mysqli_query($mysql_con, "INSERT INTO FrClass (fr_id, sub_date, severity, state, brief_des, fdt, ia, orig_proj, plan_rel, diph, ec, domain, engineer, dart, cor, rca_rept, changeset, rca_con, clone_info, submitter, reason, rcr, ont_type, business_line) VALUES('$fr_id', '$sub_date','$severity','$state','$fr_description','$fdt','$ia','$orig_proj','$plan_rel','$diph','$ec','$domain','$fr_engineer', '$dart', '$cor', '$rca', '$change_set', '$rca_contact', '$clone_info', '$submitter', '$reason', '$rcr', '$ont_type', '$business_line')")){
         log_update("insert successfully!");
     }
     else{
        log_update("insert fail with err code:" . mysqli_error($mysql_con) . " ");
     }
  }
  else { //update
		  log_update("UPDATE FrClass SET sub_date = '$sub_date',severity = '$severity',state = '$state',brief_des = '$fr_description',fdt = '$fdt',ia = '$ia',orig_proj = '$orig_proj',plan_rel = '$plan_rel',diph = '$diph',engineer = '$fr_engineer',submitter = '$submitter',reason = '$reason',rcr = '$rcr',ont_type='$ont_type' WHERE fr_id = '$fr_id'" , "yellow");
      if(mysqli_query($mysql_con, "UPDATE FrClass SET sub_date = '$sub_date',severity = '$severity',state = '$state',brief_des = '$fr_description',fdt = '$fdt',ia = '$ia',orig_proj = '$orig_proj',plan_rel = '$plan_rel',diph = '$diph',engineer = '$fr_engineer',submitter = '$submitter',reason = '$reason',rcr = '$rcr',ont_type='$ont_type',business_line='$business_line' WHERE fr_id = '$fr_id'")){
          log_update("upadte successfully!");
      }
      else{
        log_update("update fail with err code:" . mysqli_error($mysql_con) . " ");
      }
  }
}

/*
function parse_fr_html_table($html, $prj){
  $line_num = 0;
  $fr_array = array();
  $col = 0;
  $fr_list_in_web = array();
  $open_count = 0;
  global $state_open_list;
  global $fdt_filter;
  $week = get_week_number();

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
    
    if (in_array($fr_array[3], $state_open_list))
        $open_count++;

	insert_fr_to_db($fr_array); 
	array_push($fr_list_in_web, $fr_array[0]);

	$line_num++;
  }
  $line_num--;

  log_update("Total $open_count open FR for $prj!");
  $stats = get_burn_down_stats($week, $fdt_filter, $prj);
  $stats['open'] = $open_count;
  update_burn_down_stats($week, $fdt_filter, $prj, $stats);

  echo "Total $line_num FR was added or updated for $prj!\n";
  log_update("Total $line_num FR was added or updated for $prj!");
  return $fr_list_in_web;
}
*/

function get_html_page($url_fr){
  global $proxy_port;
  global $proxy_ip;
  global $passwd;

  $cookie_jar = dirname(__FILE__)."/jira.cookie";
  $headers = array('Content-Type: application/json', 'Accept: application/json', 'Authorization: Basic ' . base64_encode("dingjunh:" . $passwd));
  
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, $url_fr);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
//  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
  curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
  curl_setopt($ch, CURLOPT_PROXY, $proxy_ip); 
  curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);   

  $output = curl_exec($ch);


//  echo 'Get ' . $url_fr . '<br/>';
//  echo 'Output: ' . $output . '<br/>';
  if($output == false){
     echo 'curl_getinfo:' . curl_getinfo($ch) . '<br/>';
     echo 'curl_errno:' . curl_errno($ch) . '<br/>';
     echo 'curl_error:' . curl_error($ch) . '<br/>';
  }
  curl_close($ch);
  
  //echo base64_encode("dingjunh:Hdjmh@1013");

  return $output;
}

function get_html_page_with_proxy($url_fr){
  global $proxy_ip;
  global $proxy_port;

  $aContext = array(
    'http' => array(
//        'proxy' => "tcp://$proxy_ip:$proxy_port",
        'request_fulluri' => true,
    ),
  );
  $cxContext = stream_context_create($aContext);

  return file_get_html($url_fr, False, $cxContext);
}

function update_frs_for_one_project($prj){
  global $web_side_lnk;
  global $fdt_filter;
  global $web_side_lnk_pre, $web_side_lnk_suf_fdt, $web_side_lnk_pre_prj, $web_side_lnk_suf_prj , $web_side_lnk_last;
  global $prod, $web_side_lnk_prod,$web_side_lnk_fdt_bbdhb,$web_side_lnk_fdt_bbdprod;
  $fr_array = array();
  $fr_list_in_web = array();
  $week = get_week_number();
  $wkday = get_week_day();
  $cnt_open = 0;
  $cnt_open_critical = 0;
  $cnt_open_major = 0;
  $cnt_open_minor = 0;
  $cnt_closed = 0;
  
  $fr_number = 0;
  if($prod == "BBDPROD")
      $html_link = $web_side_lnk_pre . $web_side_lnk_prod . $prod . $web_side_lnk_fdt_bbdprod . urlencode($fdt_filter) . $web_side_lnk_suf_fdt;
  else
      $html_link = $web_side_lnk_pre . $web_side_lnk_prod . $prod . $web_side_lnk_fdt_bbdhb . urlencode($fdt_filter) . $web_side_lnk_suf_fdt;
  
  if($prj_filter != "none"){
      $html_link = $html_link . $web_side_lnk_pre_prj . $prj . $web_side_lnk_suf_prj . $web_side_lnk_last;
  }
  else {
      $html_link = $html_link . $web_side_lnk_last;
  }

  // get DOM from URL or file
  log_update("update_frs_for_one_project: before getting html $html_link!");
  $html_doc = get_html_page($html_link);
  log_update("update_frs_for_one_project: after getting html $html_link!");

  if($html_doc == FALSE){
	  log_update("update_frs_for_one_project: after getting html $html_link failed!");
    return;
  }

  log_update("update_frs_for_one_project: getting html $html_link successfully!");

  //var_dump($html_doc);
  $html_json = json_decode($html_doc);
  
  if(($html_json != null) && ($html_json->total > 0)){
      log_update("update_frs_for_one_project: FR number is " . $html_json->total . " for project " . $prj . "!");
      foreach($html_json->issues as $issue) {
          $fr_array[0] = $issue->key;
          $fr_array[1] = $issue->fields->created;
          $fr_array[2] = "";  //priority
          $fr_array[3] = $issue->fields->customfield_27892->value; //severity
          $fr_array[4] = $issue->fields->status->name;
          $fr_array[5] = $issue->fields->summary;
		  if($prod == "BBDPROD")
			  $fr_array[6] = $issue->fields->customfield_14545->value; //team
		  else
              $fr_array[6] = $issue->fields->customfield_37100->value;
          $fr_array[7] = $issue->fields->assignee->name;
          $fr_array[8] = $issue->fields->reporter->name;
          $fr_array[9] = $issue->fields->versions[0]->name;
          $fr_array[10] = $issue->fields->fixVersions[0]->name;
          $fr_array[11] = $issue->fields->customfield_32017->value; //dip
          $fr_array[12] = "";  //ec
          $fr_array[13] = "";  //domain
          $fr_array[14] = $issue->fields->assignee->name;  //engineer
          $fr_array[15] = $issue->fields->customfield_19503->child->value;  //board
		  $fr_array[16] = $issue->fields->customfield_37061[0]->value;  //product
          
          //var_dump($fr_array);
          
	      insert_fr_to_db($fr_array); 
          array_push($fr_list_in_web, $fr_array[0]);      
          //var_dump($fr_list_in_web);    
          
          if(($issue->fields->status->name == "New") || ($issue->fields->status->name == "Accepted")  || ($issue->fields->status->name == "Query")  || ($issue->fields->status->name == "Hold")){
              $cnt_open++;
              if($issue->fields->customfield_27892->value == "Critical")
              	$cnt_open_critical++;
              if($issue->fields->customfield_27892->value == "Major")
              	$cnt_open_major++;
              if($issue->fields->customfield_27892->value == "Minor")
              	$cnt_open_minor++;	
          }
          else
              $cnt_closed++;
      }

      //update the stats
      $stats = get_burn_down_stats($week, $wkday, $fdt_filter, $prj);
      $stats['handled'] = $cnt_closed;
      $stats['closed'] = $cnt_closed;
      $stats['open'] = $cnt_open;
      $stats['open_critical'] = $cnt_open_critical;
      $stats['open_major'] = $cnt_open_major;
      $stats['open_minor'] = $cnt_open_minor;
      $stats['incoming'] = 0;
      update_burn_down_stats($week, $wkday, $fdt_filter, $prj, $stats);
              
      set_fr_state_to_close($prj, $fdt_filter, $fr_list_in_web);
  }
  else{
	  //set_fr_state_to_close($prj, $fdt_filter, $fr_list_in_web);
      log_update("update_frs_for_one_project: FR number is 0 for project " . $prj . "!" . ($html_json == null)?"html_json==null":"html_json==null");
      //update the stats
      $stats = get_burn_down_stats($week, $wkday, $fdt_filter, $prj);
      $stats['handled'] = 0;
      $stats['closed'] = 0;
      $stats['open'] = 0;
      $stats['open_critical'] = 0;
      $stats['open_major'] = 0;
      $stats['open_minor'] = 0;
      $stats['incoming'] = 0;
      update_burn_down_stats($week, $wkday, $fdt_filter, $prj, $stats);	  
  }
}

function get_rel_list_for_fdt($fdt){
  global $web_side_lnk_pre, $web_side_lnk_suf_fdt, $web_side_lnk_suf_open, $web_side_lnk_last;
  global $prod, $web_side_lnk_prod,$web_side_lnk_fdt_bbdhb,$web_side_lnk_fdt_bbdprod;
  $rel_list = array();
  $x = 0;

  //$html_link = $web_side_lnk_pre . urlencode($fdt) . $web_side_lnk_suf_fdt . $web_side_lnk_suf_open . $web_side_lnk_last;
  if($prod == "BBDPROD")
      $html_link = $web_side_lnk_pre . $web_side_lnk_prod . $prod . $web_side_lnk_fdt_bbdprod . urlencode($fdt) . $web_side_lnk_suf_fdt . $web_side_lnk_suf_open . $web_side_lnk_last;
  else
      $html_link = $web_side_lnk_pre . $web_side_lnk_prod . $prod . $web_side_lnk_fdt_bbdhb . urlencode($fdt) . $web_side_lnk_suf_fdt . $web_side_lnk_suf_open . $web_side_lnk_last;
  //var_dump($html_link);
  
  log_update("get_rel_list_for_fdt with link: $html_link!");
  
  $html_doc = get_html_page($html_link);
  if($html_doc == FALSE){
	  log_update("get_rel_list_for_fdt: after getting html $html_link failed!");
    return $rel_list;
  }
  
  $html_json = json_decode($html_doc);
  //var_dump($html_json);
 
  if(($html_json != null) && ($html_json->total > 0)){
      foreach($html_json->issues as $issue) {
          $rel = $issue->fields->fixVersions[0]->name;
          if($rel == "")
              $rel = $issue->fields->versions[0]->name;
          if(!in_array($rel, $rel_list))
              array_push($rel_list, $rel);       
      }
  }
  
  //var_dump($rel_list);
  
  return $rel_list;
}

function get_open_rel_list_for_fdt_in_db($fdt){
  $rel_list = array();
  $x = 0;
  global $mysql_con;
  $week = get_week_number();
  $wkday = get_week_day();
  
  $result = mysqli_query($mysql_con, "SELECT * FROM FrClass WHERE fdt = "  . "'" . "$fdt" . "'");
  log_update("SELECT * FROM FrClass ORDER BY plan_rel WHERE fdt = "  . "'" . "$fdt" . "'");
  while($row = mysqli_fetch_array($result))
  {
    if(($row["state"] != "closed")
	   &&($row["state"] != "Duplicate")	   
	   &&($row["state"] != "Verified")
	   &&($row["state"] != "Rejected")
	   &&($row["state"] != "Unplanned")){
		if(!in_array($row["plan_rel"], $rel_list))
			$rel_list[$x++] = $row["plan_rel"];
    }
  }

  $result = mysqli_query($mysql_con, "SELECT * FROM BurnDownStats " . "WHERE week = " . "'". "$week" . "'" . " AND fdt = "  . "'" . "$fdt" . "'" . " AND wkday = "  . "'" . "$wkday" . "'");
  while($row = mysqli_fetch_array($result))
  {
    if($row["open"] != 0){
        log_update("Get " . $row["rel"] . " with open issues.");
		if(!in_array($row["rel"], $rel_list))
			$rel_list[$x++] = $row["rel"];
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
  $prt_str = "";
  foreach($rel_list as $item => $val){
		$prt_str = $prt_str . $val . ",";
  }
  //var_dump($prt_str);
  log_update("update_frs_for_all_project: open release list in web is: " . $prt_str);

  $open_rel_list = get_open_rel_list_for_fdt_in_db($fdt_filter);
  $prt_str = "";
  foreach($open_rel_list as $item => $val){
		$prt_str = $prt_str . $val . ",";
  }
  log_update("update_frs_for_all_project: open release list in db is: " . $prt_str);
  //var_dump($prt_str);

  $open_rel_list = array_merge($open_rel_list, $rel_list);
  $open_rel_list = array_unique($open_rel_list);
  $prt_str = "";
  foreach($open_rel_list as $item => $val){
		$prt_str = $prt_str . $val . ",";
  }	
  log_update("update_frs_for_all_project: open release list after merging is: " . $prt_str);
  //var_dump($prt_str);
  
  foreach($open_rel_list as $item => $val){
	  if($val != ''){
	  log_update("update_frs_for_all_project: start to update fr for release '$val' of fdt '$fdt_filter'");

      update_frs_for_one_project($val);
	  log_update("update_frs_for_all_project: end update fr for release '$val' of fdt '$fdt_filter'");
	  }
  }	
}

function get_value($field){
  $value = "";

  if($field->DataType == "SHORT_STRING"){
	  $tmp = $field->CurrentValue;
      $tmp = str_replace("\r","<br>", $tmp);
      $tmp = str_replace("\n","<br>", $tmp);      
      $value = $tmp;
   }

  if($field->DataType == "MULTILINE_STRING"){
      $arrlength = count($field->CurrentValue);
	  $value =  "<br>";
      for($x=0;$x<$arrlength;$x++) {
          $tmp =  $field->CurrentValue[$x];
          $tmp = str_replace("\r","<br>", $tmp);
          $tmp = str_replace("\n","<br>", $tmp);
          $value = $value . $tmp . "<br>";
      }
  }

  if($field->DataType == "RESOURCE_LIST"){
      $arrlength = count($field->CurrentValue);
	  $value = "";
      for($x=0;$x<$arrlength;$x++) {
          $tmp =  $field->CurrentValue[$x]->DisplayName;
          $tmp = str_replace("\r","<br>", $tmp);
          $tmp = str_replace("\n","<br>", $tmp);
          $value = $value . $tmp . "<br>";
      }
  }

  if($field->DataType == "DATE_TIME"){
	  $tmp = $field->CurrentValue;
      $value = $tmp;
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
  global $proxy_ip;
  global $proxy_port;
  $cookie_jar = dirname(__FILE__)."/pic.cookie";
  $url_fr = "http://isam-cq.web.alcatel-lucent.com/cqweb/cqartifactdetails.cq?action=GetCQRecordDetails&resourceId=cq.record%3AFR_IR%2F" . $fr_id . "%40prod%2FALU&state=VIEW&tabIndex=0&acceptAllTabsData=true&cquid=rwQJ7z0QuIkUhFL2QDYzHeC";

  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, $url_fr);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
  curl_setopt($ch, CURLOPT_PROXY, $proxy_ip); 
  curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port); 
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

if(isset($_GET["force"])){
    $force = $_GET["force"];
}

if(isset($_GET["prod"])){
    $prod = $_GET["prod"];
}
else {
    $prod = "BBDHB";	
}

$mysql_con = mysqli_connect("localhost","root","123456", "fr_db");
if (mysqli_connect_errno())
{
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

lock_for_update($fdt_filter);

log_update("Enter into upload_file_jira.php");

update_last_time();

log_update("FDT team: $fdt_filter");

if(isset($_GET["prj"]) && ($_GET["prj"] != "none")) {
  $prj_filter = $_GET["prj"];
  log_update("update_frs_for_one_project: $prj_filter");
  update_frs_for_one_project($_GET["prj"]);
}
else {
	log_update("Start to update_frs_for_all_project");
	update_frs_for_all_project();

	log_update("End to update_frs_for_all_project");
}

log_update("Exit from upload_file_jira.php<br>");

unlock_for_update();

mysqli_close($mysql_con);
?>
