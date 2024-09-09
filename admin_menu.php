<?php
include_once 'auth/auth.php';
requireAdmin();
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <a class="navbar-brand" href="plan_editor.php">Plan Editor</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="manage_volunteers.php">Manage Volunteers</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_stores.php">Manage Stores</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_tags.php">Manage Tags</a> <!-- New Tag Editor Link -->
            </li>
        </ul>
    </div>
</nav>