<?php
require_once "helper/api_utils.php";
require_once "../database/user.php";

ApiUtils\require_login();
ApiUtils\require_get_values("search_string");

$search_query = $_GET['search_string'];
if (strlen($search_query) == 0) {
    // don't complete empty usernames -> send 400 Bad Request response
    ApiUtils\send_error("Search query must not be empty!", 400);
}

$usernames = User::completeUsername($search_query);
ApiUtils\send_success($usernames);
