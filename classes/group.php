<?php 

class Group
{

	private $error = "";
 
 		public function evaluate($data)
	{

		foreach ($data as $key => $value) {
			# code...

			if(empty($value))
			{
				$this->error = $this->error . $key . " is empty!<br>";
			}

 			if($key == "group_name")
			{
				if (is_numeric($value)) {
        
 					$this->error = $this->error . "group name cant be a number<br>";
    			}
 
			}
			
			if($key == "group_type" && ($value != "Public" && $value != "Private"))
			{
         
 				$this->error = $this->error . "Please enter a valid group type<br>";
  
			}

	 
		}

		$Database = new Automated_DB();

		//check url address
		$data['url_address'] = str_replace(" ","_",strtolower($data['group_name']));

		$sql = "select id from users where url_address = '$data[url_address]' limit 1";
		$check = $Database->read_from_db($sql);
		while(is_array($check)){

			$data['url_address'] = str_replace(" ","_",strtolower($data['group_name'])) . rand(0,9999);
			$sql = "select id from users where url_address = '$data[url_address]' limit 1";
			$check = $Database->read_from_db($sql);
		}

		$data['userid'] = $this->create_userid();
		//check userid
		$sql = "select id from users where userid = '$data[userid]' limit 1";
		$check = $Database->read_from_db($sql);
		while(is_array($check)){

			$data['userid'] = $this->create_userid();
			$sql = "select id from users where userid = '$data[userid]' limit 1";
			$check = $Database->read_from_db($sql);
		}
 

		if($this->error == "")
		{

			//no error
			$this->create_group($data);
		}else
		{
			return $this->error;
		}

		
	}

	public function remove_member($groupid,$userid){

		$Database = new Automated_DB();

		$groupid = addslashes($groupid);
		$userid = addslashes($userid);

		$query = "update group_members set disabled = 1 where userid = '$userid' && groupid = '$groupid' ";
		$Database->save_query_in_db($query);

		$query = "update users set owner = 1 where userid = '$groupid' limit 1";
		
		$Database->save_query_in_db($query);
	}

	public function edit_member_access($groupid,$userid,$role){

		$Database = new Automated_DB();

		$groupid = addslashes($groupid);
		$userid = addslashes($userid);
		$role = addslashes($role);
		$me = addslashes($_SESSION['palsworld_userid']);
		
		$query = "update group_members set role = '$role' where userid = '$userid' && groupid = '$groupid' ";
		$Database->save_query_in_db($query);
	
		//notify user of this change
 		$row = $this->get_group($groupid);
 		if(is_array($row)){

 			$row = $row[0];
 			$row['owner'] = $userid;
 			add_notification($me,"role",$row);
 		}
	}

	public function get_member_role($groupid,$userid){

		$Database = new Automated_DB();
		$role = "Unknown";

		$groupid = addslashes($groupid);
		$userid = addslashes($userid);

		$query = "select role from group_members where userid = '$userid' && groupid = '$groupid' && disabled = 0";
		$result = $Database->read_from_db($query);
		if(is_array($result)){
			return $result[0]['role'];
		}

		$query = "select id from users where userid = '$groupid' && owner = '$userid' limit 1";
		$result = $Database->read_from_db($query);
		if(is_array($result)){
			return "admin";
		}

		return $role;
	}

	
	public function create_group($data)
	{

		$group_name = ucfirst(addslashes($data['group_name']));
		$userid = $data['userid'];
		$url_address = $data['url_address'];
		$type = 'group';
		$group_type = addslashes($data['group_type']);
		$date = date("Y-m-d H:i:s");
		$owner = addslashes($_SESSION['palsworld_userid']);

		//create these
		$url_address = strtolower($group_name) . "." . rand(0,9999);

		$query = "insert into users 
		(userid,type,group_type,username,url_address,date,owner) 
		values 
		('$userid','$type','$group_type','$group_name','$url_address','$date','$owner')";

		$Database = new Automated_DB();
		$Database->save_query_in_db($query);
	}
 
 	public function join_group($groupid,$userid){

 		$Database = new Automated_DB();
 		$groupid = esc($groupid);
 		$userid = esc($userid);

 		$query = "select * from group_requests where userid = '$userid' && groupid = '$groupid' limit 1";
		$check = $Database->read_from_db($query);

 		if($check){
 			$check = $check[0];
 			$query = "update group_requests set disabled = 0 where id = '$check[id]' limit 1";
 		}else{
 			$query = "insert into group_requests (groupid,userid) values ('$groupid','$userid')";
 		}

		$Database->save_query_in_db($query);
 	}

 	public function accept_request($groupid,$userid,$action){

 		$Database = new Automated_DB();
 		$groupid = esc($groupid);
 		$userid = esc($userid);
 		$action = esc($action);
 		$role = "member";

 		if($action == "accept"){
	 		
	 		$query = "select * from group_members where userid = '$userid' && groupid = '$groupid' limit 1";
	 		$check = $Database->read_from_db($query);

	 		if($check){
	 			$check = $check[0];
	 			$query = "update group_members set disabled = 0 where id = '$check[id]' limit 1";
				$DB->save($query);
				
	 		}else{
	 			$query = "insert into group_members (groupid,userid,role) values ('$groupid','$userid','$role')";
				$Database->save_query_in_db($query);
	 		}

		}
		
		$query = "update group_requests set disabled = 1 where  userid = '$userid' && groupid = '$groupid' limit 1";
		$Database->save_query_in_db($query);

		$query = "update group_invites set disabled = 1 where  userid = '$userid' && groupid = '$groupid' limit 1";
		$Database->save_query_in_db($query);
	
 	}


 	public function get_invited($groupid){

 		$Database = new Automated_DB();

 		$groupid = addslashes($groupid);
 		$me = addslashes($_SESSION['palsworld_userid']);
 		$query = "select * from group_invites where groupid = '$groupid' && userid = '$me' && disabled = 0 ";
 		$check = $Database->read_from_db($query);
 		if(is_array($check)){

 			return $check;
 		}

 		return false;
 	}

 	public function invite_to_group($groupid,$userid,$me){

 		$groupid = addslashes($groupid);
 		$userid = addslashes($userid);
 		$me = addslashes($me);

 		$Database = new Automated_DB();
 		
 		$query = "select * from group_invites where groupid = '$groupid' && userid = '$userid' && inviter = '$me' ";
 		$check = $Database->read_from_db($query);
 		if(is_array($check)){

 			$id = $check[0]['id'];
 			$query = "update group_invites set disabled = 0 where id = '$id' limit 1";
 			$check = $Database->save_query_in_db($query);
 			
 		}else{
 			$query = "insert into group_invites (groupid,userid,inviter) values ('$groupid','$userid','$me')";
 			$check = $Database->save_query_in_db($query);
 		}

 		//notify user of invitation
 		$row = $this->get_group($groupid);
 		if(is_array($row)){

 			$row = $row[0];
 			$row['owner'] = $userid;
 			add_notification($me,"invite",$row);
 		}


 	}
 	

 	public function get_requests($groupid){

 		$Database = new Automated_DB();
 		$groupid = esc($groupid);

 		$query = "select * from group_requests where groupid = '$groupid' && disabled = 0 ";
 		$check = $Database->read_from_db($query);

 		if($check){
 			return $check;
 		}

 		return false;

 	}

 	public function get_members($groupid,$limit = 100){

 		$Database = new Automated_DB();
 		$groupid = esc($groupid);

 		$query = "select owner from users where userid = '$groupid' limit 1";
 		$check1 = $Database->read_from_db($query);
 		$result = false;

 		if($check1){
			
			$check1[0]['userid'] = $check1[0]['owner'];
			$check1[0]['role'] = "admin";
			
			$result = $check1;
			$query = "select * from group_members where groupid = '$groupid' && disabled = 0 limit $limit";
	 		$check = $Database->read_from_db($query);

	 		if($check){

	 			$result = array_merge($check1, $check);
	 			return $result;
	 		}

	 		return $result;
 		}


 		return false;

 	}

 	public function get_invites($group_id,$id,$type){

 		$group_id = addslashes($group_id);

		$Database = new Automated_DB();
		$type = addslashes($type);

		if(is_numeric($id)){
 
			//get like details
			$sql = "select likes from likes where type='$type' && contentid = '$id' limit 1";
			$result = $Database->read_from_db($sql);
			if(is_array($result)){

				$likes = json_decode($result[0]['likes'],true);

				//get all members of the group
				$members = $this->get_members($group_id,100000);
				if(is_array($members)){

					$members = array_column($members, "userid");
					if(is_array($likes)){
						foreach ($likes as $key => $like) {
							# code...
							if(in_array($like['userid'], $members)){
								unset($likes[$key]);
							}
						}

						$likes = array_values($likes);
					}
				}
				return $likes;
			}
		}


		return false;
	}

	private function create_userid()
	{

		$length = rand(4,19);
		$number = "";
		for ($i=0; $i < $length; $i++) { 
			# code...
			$new_rand = rand(0,9);

			$number = $number . $new_rand;
		}

		return $number;
	}

	public function get_my_groups($owner)
	{

		$Database = new Automated_DB();
		$query = "select * from users where owner = '$owner' && type = 'group' ";
		$result = $Database->read_from_db($query);

		//check in group members as well
		$query = "select * from group_members where disabled = 0 && userid = '$owner' ";
		$result2 = $Database->read_from_db($query);

		if(is_array($result2))
		{
			$groupids = array_column($result2, "groupid");
			$groupids = "'" . implode("','", $groupids) . "'";
			
			//check in group members as well
			$query = "select * from users where userid in ($groupids) && type = 'group' ";
			$group_rows = $Database->read_from_db($query);

			if(is_array($group_rows))
			{
				foreach ($group_rows as $row) {
					# code...
					$result[] = $row;
				}
			}

		}

		if($result)
		{

			return $result;
		}else
		{
			return false;
		}
	}

	function get_group($id){

		$id = addslashes($id);
		$Database = new Automated_DB();
		$query = "select * from users where userid = '$id' && type = 'group' limit 1";
		return $Database->read_from_db($query);

	}

}
