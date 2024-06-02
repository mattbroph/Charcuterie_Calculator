<?php
    session_start();

    // If the user is logged in, delete session variables and redirect to the home page
    if (isset($_SESSION['user_id']))
    {
        $_SESSION = array();
        session_destroy();
    }

    // Redirect to the home page
    $home_url = dirname($_SERVER['PHP_SELF']);
    header('Location: ' . $home_url);
    exit;
