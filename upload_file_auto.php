<?php
// example of how to use basic selector to retrieve HTML contents
include('../simplehtmldom/simple_html_dom.php');

//$web_side_lnk = "http://devws165.be.alcatel-lucent.com:8889/query/";
$web_side_lnk = "http://aww.sh.bel.alcatel.be/metrics/datawarehouse/query/";
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

function get_week_day(){
    return (idate('w') == 0)?7:idate('w');
}

function get_burn_down_stats($week, $day, $fdt, $rel){
  global $mysql_con;
  log_update("get_burn_down_stats for: " . $week . $fdt . $rel);

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
  $closed = $stats['closed'];

  log_update("UPDATE BurnDownStats SET incoming = '$incoming',handled = '$handled',open = '$open' WHERE week = '$week' AND fdt = '$fdt' AND rel = '$rel'  AND day = '$day'");
  mysqli_query($mysql_con, "UPDATE BurnDownStats SET incoming = '$incoming',handled = '$handled',open = '$open',closed = '$closed' WHERE week = '$week' AND fdt = '$fdt' AND rel = '$rel'  AND wkday = '$day'");

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

  log_update("set_fr_state_to_close for prj:" . $prj . ", fdt:" . $fdt);
  //$con = mysql_connect("localhost","root","123456");
  //if (!$con)
  //{
  //  die('Could not connect: ' . mysql_error());
  //}
  //mysql_select_db("fr_db", $con);

  if($prj == "none")
    $result = mysqli_query($mysql_con, "SELECT * FROM FrClass "  . "WHERE fdt = " . "'" . "$fdt" . "'" );
  else
    $result = mysqli_query($mysql_con, "SELECT * FROM FrClass " . "WHERE plan_rel = " . "'". "$prj" . "'" . " AND fdt = "  . "'" . "$fdt" . "'"  );

  while($row = mysqli_fetch_array($result))
  {
	//log_update("Checking " . $row["fr_id"] . " in web list or not!");
    if(!in_array($row["fr_id"], $fr_list_in_web)){
		$fr_id = $row["fr_id"];
		//mysqli_query( $mysql_con, "UPDATE FrClass SET state = 'closed' "  . "WHERE fr_id = " . "'" . "$fr_id" . "'" );
		mysqli_query( $mysql_con, "DELETE FROM FrClass "  . "WHERE fr_id = " . "'" . "$fr_id" . "'");
		//log_update($fr_id . " is not in web list, will close it!");
              
    }
  }
  //mysql_close($con);
  return "OK";
}
function is_skipped($state, $engineer, $sev, $row, $fdt, $plan_rel, $ec){
    log_update("is_skipped: " . $row["state"] . "=" . $state  . "? " . $row["engineer"] . "=" . $engineer . "? " . $row["severity"] . "=" . $sev . "? " . $row["fdt"] . "=" . $fdt . "? " . $row["plan_rel"] . "=" . $plan_rel . "?" . $row["ec"] . "=" . $ec . "?");
	if(($state == $row["state"]) && ($engineer == $row["engineer"]) && ($sev == $row["severity"])  && ($fdt == $row["fdt"])  && ($plan_rel == $row["plan_rel"]) && ($row["ont_type"] != NULL)  && ($row["ec"] == $ec))
    //if(($state == $row["state"])  && ($sev == $row["severity"])  && ($fdt == $row["fdt"])  && ($plan_rel == $row["plan_rel"]))
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
  $ont_type = ""; 
  global $is_skip_updating_for_same_state;
  global $state_open_list;
  global $fdt_filter;
  global $mysql_con;
  $week = get_week_number();
  $is_fr_exist = false;
  global $force;

  //$con = mysql_connect("localhost","root","123456");
  //if (!$con)
  //{
  //  die('Could not connect: ' . mysql_error());
  //}
  //mysql_select_db("fr_db", $con);
  
  //check whether it is needed for update
  log_update("Updating FR : " . $fr_id . ",new state is : " . $state);
  if($is_skip_updating_for_same_state){
	  $result = mysqli_query($mysql_con, "SELECT * FROM FrClass WHERE fr_id = '$fr_id'");
	  $row = mysqli_fetch_array($result);

      //update the stats
      //$stats = get_burn_down_stats($week, $fdt_filter, $plan_rel);
      if($row){
          $is_fr_exist = true;
          //if(in_array($row["state"], $state_open_list) && !in_array($state, $state_open_list))
              //$stats['handled']++;
      }
      else{
         //$stats['incoming']++;
      }
      //update_burn_down_stats($week, $fdt_filter, $plan_rel, $stats);

	  log_update("Updating FR : " . $fr_id . ",current state is : " . $row["state"]);
	  if($row && is_skipped($state, $engineer, $severity, $row, $fdt, $plan_rel, $ec) && ($force == "no")){ 
        //mysql_close($con);
		    log_update("Skip FR : " . $fr_id);
		    return;
	  }
  }

  $fr_details = get_fr_details($fr_id);
  $cor_no_cor_reason = get_fr_field($fr_details, "ReasonNoCodeReview");
  $cor_flag =  get_fr_field($fr_details, "ConductCodeReview");
  $cor_type =  get_fr_field($fr_details, "TypeOfCodeReview");
  $cor_rev_board_id =  get_fr_field($fr_details, "ReviewBoardID");
  $cor_peer_review_by =  get_fr_field($fr_details, "PeerReviewPairProgramBy");
  $rcr =  get_fr_field($fr_details, "RCRWithBug");
  $reason =  get_fr_field($fr_details, "ReasonForProblem");
  $ont_type =  get_fr_field($fr_details, "ONT_Type");  
  $deliverby =  get_fr_field($fr_details, "DeadlineForDelivery");  
  log_update("Deliver FR by " . $deliverby);
  
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
  $cor = mysqli_real_escape_string($mysql_con, $cor);

  $rca = get_fr_field($fr_details, "DetailedRCAReport");
  //$rca = str_replace("'","\'", $rca);
  $rca = mysqli_real_escape_string($mysql_con, $rca);

  $clone_info = get_fr_field($fr_details, "CloneInfo");

  $change_set = get_fr_field($fr_details, "ChangeSet");
  $rca_contact = get_fr_field($fr_details, "RCAContact");
  $dart = str_replace("\r","",get_dart($fr_details, "$plan_rel"));
  $dart = str_replace("\n","<br>", $dart);
  $dart = str_replace("'","\'", $dart);
  //$fr_description = str_replace("'","\'", $fr_description);

  $fr_description = htmlentities($description, ENT_QUOTES);
  if(strlen($engineer) <= 1)
    $fr_engineer = "";
  else
    $fr_engineer = $engineer;

  //$result1 = mysqli_query($mysql_con,"SELECT * FROM FrClass WHERE fr_id = '$fr_id'");
  //if(!mysqli_fetch_array($result1)){ 
   if(!$is_fr_exist){
	 log_update("INSERT INTO FrClass (fr_id, sub_date, severity, state, brief_des, fdt, ia, orig_proj, plan_rel, diph, ec, domain, engineer, dart, cor, rca_rept, changeset, rca_con, clone_info, submitter, reason, rcr, ont_type) VALUES('$fr_id', '$sub_date','$severity','$state','$fr_description','$fdt','$ia','$orig_proj','$plan_rel','$diph','$ec','$domain','$fr_engineer', '$dart', '$cor', '$rca', '$change_set', '$rca_contact', '$clone_info', '$submitter', '$reason', '$rcr', '$ont_type')", "red");
     //log_update($mysql_con, "INSERT INTO FrClass (fr_id, sub_date, severity, state, brief_des, fdt, ia, orig_proj, plan_rel, diph, engineer, submitter, reason, rcr, ont_type) VALUES('$fr_id', '$sub_date','$severity','$state','$fr_description','$fdt','$ia','$orig_proj','$plan_rel','$diph','$fr_engineer', '$submitter', '$reason', '$rcr', '$ont_type')");
     if(mysqli_query($mysql_con, "INSERT INTO FrClass (fr_id, sub_date, severity, state, brief_des, fdt, ia, orig_proj, plan_rel, diph, ec, domain, engineer, dart, cor, rca_rept, changeset, rca_con, clone_info, submitter, reason, rcr, ont_type) VALUES('$fr_id', '$sub_date','$severity','$state','$fr_description','$fdt','$ia','$orig_proj','$plan_rel','$diph','$ec','$domain','$fr_engineer', '$dart', '$cor', '$rca', '$change_set', '$rca_contact', '$clone_info', '$submitter', '$reason', '$rcr', '$ont_type')")){
     //if(mysqli_query($mysql_con, "INSERT INTO FrClass (fr_id, sub_date, severity, state, brief_des, fdt, ia, orig_proj, plan_rel, diph, engineer, submitter, reason, rcr, ont_type) VALUES('$fr_id', '$sub_date','$severity','$state','$fr_description','$fdt','$ia','$orig_proj','$plan_rel','$diph','$fr_engineer', '$submitter', '$reason', '$rcr', '$ont_type')")){
         log_update("insert successfully!");
     }
       else{
        log_update("insert fail with err code:" . mysqli_error($mysql_con) . " ");
       }
  }
  else {
        //log_update("UPDATE FrClass SET sub_date = '$sub_date',severity = '$severity',state = '$state',brief_des = '$fr_description',fdt = '$fdt',ia = '$ia',orig_proj = '$orig_proj',plan_rel = '$plan_rel',diph = '$diph',ec = '$ec',domain = '$domain',engineer = '$fr_engineer',dart = '$dart',cor = '$cor',rca_rept = '$rca',changeset = '$change_set',rca_con = '$rca_contact',clone_info = '$clone_info',submitter = '$submitter',reason = '$reason',rcr = '$rcr',ont_type='$ont_type' WHERE fr_id = '$fr_id'" , "yellow");
		log_update("UPDATE FrClass SET sub_date = '$sub_date',severity = '$severity',state = '$state',brief_des = '$fr_description',fdt = '$fdt',ia = '$ia',orig_proj = '$orig_proj',plan_rel = '$plan_rel',diph = '$diph',engineer = '$fr_engineer',submitter = '$submitter',reason = '$reason',rcr = '$rcr',ont_type='$ont_type' WHERE fr_id = '$fr_id'" , "yellow");
		//if(mysqli_query($mysql_con, "UPDATE FrClass SET sub_date = '$sub_date',severity = '$severity',state = '$state',brief_des = '$fr_description',fdt = '$fdt',ia = '$ia',orig_proj = '$orig_proj',plan_rel = '$plan_rel',diph = '$diph',ec = '$ec',domain = '$domain',engineer = '$fr_engineer',dart = '$dart',cor = '$cor',rca_rept = '$rca',changeset = '$change_set',rca_con = '$rca_contact',clone_info = '$clone_info',submitter = '$submitter',reason = '$reason',rcr = '$rcr',ont_type='$ont_type' WHERE fr_id = '$fr_id'")){
        if(mysqli_query($mysql_con, "UPDATE FrClass SET sub_date = '$sub_date',severity = '$severity',state = '$state',brief_des = '$fr_description',fdt = '$fdt',ia = '$ia',orig_proj = '$orig_proj',plan_rel = '$plan_rel',diph = '$diph',ec = '$ec',engineer = '$fr_engineer',submitter = '$submitter',reason = '$reason',rcr = '$rcr',ont_type='$ont_type' WHERE fr_id = '$fr_id'")){
        log_update("upadte successfully!");
    }
    else{

        log_update("update fail with err code:" . mysqli_error($mysql_con) . " ");
    }

 }

  //mysql_close($con);
}

function parse_fr_html_table($html, $prj){
  $line_num = 0;
  $fr_array = array();
  $col = 0;
  $fr_list_in_web = array();
  $open_count = 0;
  global $state_open_list;
  global $fdt_filter;
  $week = get_week_number();
  $wkday = get_week_day();
  $cnt_open = 0;
  $cnt_closed = 0;
  
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
    
        if (in_array($fr_array[4], $state_open_list)){
            $open_count++;
	    $cnt_open++;
	}
	else{
	    $cnt_closed++;
	}

	insert_fr_to_db($fr_array); 
	array_push($fr_list_in_web, $fr_array[0]);

	$line_num++;
  }
  $line_num--;

  log_update("Total $open_count open FR for $prj!");
      $stats = get_burn_down_stats($week, $wkday, $fdt_filter, $prj);
      $stats['handled'] = $cnt_closed;
      $stats['closed'] = $cnt_closed;
      $stats['open'] = $cnt_open;
      $stats['incoming'] = 0;
      update_burn_down_stats($week, $wkday, $fdt_filter, $prj, $stats);

  echo "Total $line_num FR was added or updated for $prj!\n";
  log_update("Total $line_num FR was added or updated for $prj!");
  return $fr_list_in_web;
}

function get_html_page($url_fr){
  global $proxy_port;
  global $proxy_ip;

  $cookie_jar = dirname(__FILE__)."/pic.cookie";

  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, $url_fr);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
  curl_setopt($ch, CURLOPT_PROXY, $proxy_ip); 
  curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);     
  $output = curl_exec($ch);
  curl_close($ch);

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
  $week = get_week_number();
  $wkday = get_week_day();
  $is_found = false;

  $fr_number = 0;
  $html_link = $web_side_lnk . "FRStatus.cgi?changeFormDB=&project=" . "$prj" . "&origproj=&team=" . "$fdt_filter" . "&tp=&states=NXQHLBRVJUD&per=FDT&from=&till=&site=&phase=&haby=&action=Apply";
  //get_burn_down_stats(get_week_number(), $fdt_filter, $prj);

  if($prj == "none")
     $html_link = $web_side_lnk . "FRStatus.cgi?changeFormDB=&project=&origproj=&team=" . "$fdt_filter" . "&tp=&states=NXQHLBRVJUD&per=FDT&from=&till=&site=&phase=&haby=&action=Apply";

  // get DOM from URL or file
  log_update("update_frs_for_one_project: before getting html $html_link!");
  $html_doc = get_html_page_with_proxy($html_link);
  log_update("update_frs_for_one_project: after getting html $html_link!");

   if($html_doc == FALSE){
	  log_update("update_frs_for_one_project: after getting html $html_link failed!");
      return;
  }

  foreach($html_doc->find('th') as $element) {
    if($element->firstChild() == null)
        continue;
	if($element->firstChild()->innertext == "Total"){
		  //$html_link = $web_side_lnk . $element->parent()->childNodes(7)->firstChild()->href;
                  $html_link = $element->parent()->childNodes(7)->firstChild()->href;
		  $is_found = true;
		  break;
	}
  }
  if($is_found != true){
	  log_update("update_frs_for_one_project: exist beacuse no Total link is not found!");
	  return;
  }

  log_update("update_frs_for_one_project: before getting html $html_link!");
  $html_tmp =  get_html_page_with_proxy($html_link);
  log_update("update_frs_for_one_project: after getting html $html_link!");

   if($html_tmp == FALSE){
	  log_update("update_frs_for_one_project: after getting html $html_link failed!");
      return;
  }

  if($html_tmp){
    $fr_list_in_web = parse_fr_html_table($html_tmp, $prj);
	//if(count($fr_list_in_web) != 0)
	    set_fr_state_to_close($prj, $fdt_filter, $fr_list_in_web);
  }
}

function get_rel_list_for_fdt($fdt){
  global $web_side_lnk;
  $rel_list = array();
  $x = 0;

  $html_link = $web_side_lnk . "FRStatus.cgi?changeFormDB=&project=&origproj=&team=" . "$fdt" . "&tp=&states=NXQH&per=FDT&from=&till=&site=&phase=&haby=&action=Apply";

  log_update("get_rel_list_for_fdt: open release list in web is: " . $html_link);

  // get DOM from URL or file
  $html_doc = get_html_page_with_proxy($html_link);
  if($html_doc == FALSE){
	  log_update("get_rel_list_for_fdt: get DOM from URL failed!");
      return $rel_list;
  }
  log_update("get_rel_list_for_fdt: get DOM from URL success.");

  foreach($html_doc->find('th') as $element) {
    if($element->firstChild() == null)
        continue;
	if($element->firstChild()->innertext == "$fdt"){
		$prj = $element->parent()->childNodes(3)->innertext;
		if(!is_nwf_fdt($fdt)){
			log_update("get_rel_list_for_fdt: None NWF fdt:" . $fdt);
			$rel_list[$x++] = $prj;
		}
		else if(is_nwf_prj($prj)){
		    log_update("get_rel_list_for_fdt: NWF fdt:" . $fdt . "prj:" . $prj);
            $rel_list[$x++] = $prj;
		}
		log_update("get_rel_list_for_fdt:find one project $prj for $fdt.");
	}
	else
	    log_update("get_rel_list_for_fdt: find one th not for $fdt.");
  }
  log_update("get_rel_list_for_fdt: get openned releases list.");
  return $rel_list;
}

function get_open_rel_list_for_fdt_in_db($fdt){
  $rel_list = array();
  $x = 0;
  global $mysql_con;
  //$con = mysql_connect("localhost","root","123456");
  //if (!$con)
  //{
  //  die('Could not connect: ' . mysql_error());
  //}
  //mysql_select_db("fr_db", $con);

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

  //mysql_close($con);

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
  log_update("update_frs_for_all_project: open release list in web is: " . $prt_str);

  $open_rel_list = get_open_rel_list_for_fdt_in_db($fdt_filter);
  $prt_str = "";
  foreach($open_rel_list as $item => $val){
		$prt_str = $prt_str . $val . ",";
  }
  log_update("update_frs_for_all_project: open release list in db is: " . $prt_str);

  $open_rel_list = array_merge($open_rel_list, $rel_list);
  $open_rel_list = array_unique($open_rel_list);
  $prt_str = "";
  foreach($open_rel_list as $item => $val){
		$prt_str = $prt_str . $val . ",";
  }	
  log_update("update_frs_for_all_project: open release list after merging is: " . $prt_str);

	/*
  for($x = 0; $x < count($open_rel_list); $x++){
	  if($open_rel_list[$x] != ''){
	  log_update("update_frs_for_all_project: start to update fr for release '$open_rel_list[$x]' of fdt '$fdt_filter'");

      update_frs_for_one_project($open_rel_list[$x]);
	  log_update("update_frs_for_all_project: end update fr for release '$open_rel_list[$x]' of fdt '$fdt_filter'");
	  }
  }
  */
  foreach($open_rel_list as $item => $val){
	  if($val != ''){
	  log_update("update_frs_for_all_project: start to update fr for release '$val' of fdt '$fdt_filter'");

      update_frs_for_one_project($val);
	  log_update("update_frs_for_all_project: end update fr for release '$val' of fdt '$fdt_filter'");
	  }
  }	
}

function login_cq(){
  global $passwd;
  global $proxy_port;
  global $proxy_ip;
  $cookie_jar = dirname(__FILE__)."/pic.cookie";
  $url = "http://isam-cq.web.alcatel-lucent.com/cqweb/cqlogin.cq";
  $post_data = array ("action" => "DoLogin", "loginId" => "dingjun he","password" => $passwd, "repository" => "prod", "tzOffset" => "GMT+8:00", "loadAllRequiredInfo" => "true", "userDb" => "ALU", "cquid" => "rwQJ7z0QuIkUhFL2QDYzHeC");
  //$post_data = array ("action" => "DoLogin", "loginId" => "dingjun he","password" => $passwd, "repository" => "prod");
  //log_update("login_cq with '$url'");
  //log_update("login_cq with '$post_data'");
  $ch = curl_init();
  //log_update("login_cq after curl_init");
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
  curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
  curl_setopt($ch, CURLOPT_PROXY, $proxy_ip); 
  curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port); 
  curl_exec($ch);
    //log_update("login_cq after curl_exec");
  curl_close($ch);
  //log_update("login_cq after curl_close");
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

$mysql_con = mysqli_connect("localhost","root","123456", "fr_db");
if (mysqli_connect_errno())
{
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

lock_for_update($fdt_filter);

log_update("Enter into upload_file_auto.php");

update_last_time();

login_cq();

log_update("Logined CQ");

if(isset($_GET["force"]))
	$force = $_GET["force"];
else
   $force = "no";

if(isset($_GET["prj"]) && ($_GET["prj"] != "none")) {
  log_update("update_frs_for_one_project");
  update_frs_for_one_project($_GET["prj"]);
}
else {
	log_update("Start to update_frs_for_all_project");
	update_frs_for_all_project();

    //$html_test = get_html_page_with_proxy( "http://aww.sh.bel.alcatel.be/metrics/datawarehouse/query/FRStatus.cgi?states=NXQH&per=FDT&team=1252,1486,1559,1574,BCMBL,GLMTK&pt=&nothaby=");
    //$html_test = file_get_html("http://135.251.205.199:8086/Build_Delivery_Page/displayRelease/list_All.do");
    //echo($html_test);
    //echo("TEST7");
	log_update("End to update_frs_for_all_project");

}

log_update("Exit from upload_file_auto.php<br>");

unlock_for_update();

mysqli_close($mysql_con);
?>
