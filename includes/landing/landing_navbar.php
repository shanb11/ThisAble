<?php
// Define the absolute base URL of your project
// Change this to match your setup
$base_url = '/thisable/';

// For the active class detection
$current_file = basename($_SERVER['PHP_SELF']);
?>

<header>
    <div class="container">
        <div class="logo">
            <img src="<?php echo $base_url; ?>images/thisablelogo.png" alt="ThisAble Logo">
        </div>
        <nav>
            <ul>
                <li><a href="<?php echo $base_url; ?>index.php" class="<?php echo $current_file == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="<?php echo $base_url; ?>frontend/landing/landing_jobs.php" class="<?php echo $current_file == 'landing_jobs.php' ? 'active' : ''; ?>">Jobs</a></li>
                <li><a href="<?php echo $base_url; ?>frontend/landing/landing_about.php" class="<?php echo $current_file == 'landing_about.php' ? 'active' : ''; ?>">About</a></li>
            </ul>
        </nav>
        <div class="sign-in">
            <a href="<?php echo $base_url; ?>frontend/candidate/login.php" class="btn btn-primary">Sign In</a>
        </div>
    </div>
</header>