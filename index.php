<?php
require_once 'auth/auth.php';
requireLogin();

if (isAdmin()) {
    include 'admin_menu.php'; // Include the admin menu
    echo "<h1>Welcome, Admin</h1>";
    echo "<p>You can edit the volunteer schedule below:</p>";
    // Include the plan editing interface
    include 'plan_editor.php';

} else {
    echo "<h1>Welcome, Volunteer</h1>";
    echo "<p>You can view your assigned schedule below:</p>";
    
    // Display read-only view of the plan for volunteers
    include 'view_schedule.php';
}