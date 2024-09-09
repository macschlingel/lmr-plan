<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title ?? 'BLMR Rettungsplan'; ?></title>
    <!-- Include the main CSS file -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            padding-top: 56px; /* Height of the navbar */
        }
        .navbar {
            z-index: 1030; /* Make sure it stays on top */
        }
        .container-fluid {
            max-width: 100%;
            padding: 0 15px;
        }
        .main-content {
            overflow-x: auto; /* Allow horizontal scrolling if needed */
        }
    </style>
</head>
<body>
    <?php include_once 'admin_menu.php'; ?> <!-- Include the admin menu -->
    <div class="container-fluid main-content mt-4">