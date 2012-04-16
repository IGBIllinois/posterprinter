<?php
/////////////////////////////////////////////////
//
//	Poster Printer Order Submission		
//	authentication.inc.php
//
//	Functions to verify IGB users
//
//	David Slater
//	April 2007
//
/////////////////////////////////////////////////

function authenticate($username,$password,$ldaphost,$base_dn,$people_ou,$group_ou,$group,$ssl,$port) {
	
	$connect;
	if ($ssl == 1) { $connect = ldap_connect("ldaps://" . $ldaphost,$port); }
	elseif ($ssl == 0) { $connect = ldap_connect("ldap://" . $ldaphost,$port); }
	$bindDN = "uid=" . $username . "," . $people_ou;
	$bind_success = @ldap_bind($connect, $bindDN, $password);
	$success = 0;
	if ($bind_success) {
		$filter = "(&(cn=" . $group . ")(memberUid=" . $username . "))";
		$search = ldap_search($connect,$group_ou,$filter);
		$result = ldap_get_entries($connect,$search);
		if ($result["count"]) { $success = 1; }
	}
	ldap_unbind($connect);
	return $success;
}

?>
