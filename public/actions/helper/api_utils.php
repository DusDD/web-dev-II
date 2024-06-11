<?php
namespace ApiUtils;

require_once "../../database/user_session.php";

function send_error(string $error_message, int $status = 422,  bool $exit = true): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo $error_message;
    /*
    $response = array(
        "message" => $error_message
    );
    echo json_encode($response);
    */
    if ($exit) exit();
}

function send_success($data = null, bool $exit = true): void
{
    http_response_code(200);
    if ($data != null) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
    }
    if ($exit) exit();
}

function require_get_values(string ...$keys): void
{
    foreach ($keys as $key) {
        if (!isset($_GET[$key])) {
            send_error("Missing POST value: " . $key);
        }
    }
}

function require_post_values(string ...$keys): void
{
    foreach ($keys as $key) {
        if (!isset($_POST[$key])) {
            send_error("Missing POST value: " . $key);
        }
    }
}

function require_login(): int
{
    if (!\UserSession\isLoggedIn()) {
        http_response_code(401);
        exit();
    }
    return \UserSession\getUserId();
}