<?php
session_start();
require_once 'includes/functions.php';

// Clear all session variables
session_unset();
session_destroy();

eader('Location: ' . BASE_URL . 'index.php');
exit();
?>
// Redirect to home page
h