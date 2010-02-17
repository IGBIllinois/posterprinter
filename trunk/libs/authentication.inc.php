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
	$success = @ldap_bind($connect, $bindDN, $password);

	if ($success == 1) {
	
		$search = ldap_search($connect,$group_ou,"(cn=" . $group . ")");
		$data = ldap_get_entries($connect,$search);
		ldap_unbind($connect);
		foreach($data[0]['memberuid'] as $groupMember) {
			if ($username == $groupMember) {
				$success = 1;
				return $success;
			}
			else { $success = 0; }
		}
	}
	return $success;
	
}

?>
