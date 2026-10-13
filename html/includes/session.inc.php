<?php
//////////////////////////////////////////////////
//                                              //
//  session.inc.php                             //
//                                              //
//  Used to verify the user has a valid         //
//  session before proceeding.                  //
//                                              //
//  Fix vs. original: every failure branch      //
//  now exits after the redirect header.        //
//  Previously the page kept executing with a   //
//  destroyed session (expired timeout, IP      //
//  mismatch, and session-ID mismatch all fell  //
//  through into the page body).                //
//                                              //
//////////////////////////////////////////////////

$session = new \IGBIllinois\session(settings::get_session_name());

// Accept the session id from GET (page loads) or POST (api.php calls).
$request_session_id = $_GET['session'] ?? $_POST['session'] ?? '';

//If session timeout is reached
if (time() > $session->get_var('timeout') + settings::get_session_timeout()) {
    $session->destroy_session();
    header('Location: index.php');
    exit;
}
//If IP address is different
elseif ($_SERVER['REMOTE_ADDR'] != $session->get_var('ipaddress')) {
    $session->destroy_session();
    header('Location: index.php');
    exit;
}
elseif ($request_session_id === '' || $request_session_id != $session->get_session_id()) {
    $session->destroy_session();
    header('Location: index.php');
    exit;
}
else {
    //Reset Timeout
    $session_vars = array('timeout' => time());
    $session->set_session($session_vars);
}
