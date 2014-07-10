<?php
function UserID2UserName($id)
{
	$user_name = mysql_query("select * FROM users WHERE ID=".$id);
	if (@mysql_num_rows($user_name) > 0) {
		$row_user_name = mysql_fetch_array($user_name);
		return $row_user_name['username'];
	}
	return false;
}

function UserName2UserID($username)
{
	$user_id = mysql_query("select * FROM users WHERE username='".$username."'");
	if (@mysql_num_rows($user_id) > 0) {
		$row_user_id = mysql_fetch_array($user_id);
		return $row_user_id['ID'];
	}
return false;
}

function UserID2Email($id)
{
	$user_name = mysql_query("select * FROM users WHERE ID=".$id);
	if (@mysql_num_rows($user_name) > 0) {
		$row_user_name = mysql_fetch_array($user_name);
		return $row_user_name['email'];
	}
return false;
}

function GetUserCredit($id)
{
	$result_credit = mysql_query("select * FROM users_extension WHERE UserID=".$id);
	if (@mysql_num_rows($result_credit) == 1) {
	$row_credit = mysql_fetch_array($result_credit);
		return $row_credit['credit'];
	}
return false;
}

