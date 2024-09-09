<?php
session_start();

/**
 * Check if a user is logged in.
 * 
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if the logged-in user is an admin.
 * 
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

/**
 * Redirect to login if the user is not logged in.
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Deny access if the user is not an admin.
 */
function requireAdmin() {
    if (!isAdmin()) {
        echo "Access denied: Admins only.";
        exit();
    }
}