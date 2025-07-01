<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar" id="sidebar">
    <a href="index.html" class="logo">
        <img src="../../images/thisablelogo.png" alt="ThisAble Logo">
    </a>
    <div class="sidebar-menu">
        <a href="empdashboard.php" class="menu-item <?= $currentPage == 'empdashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span class="menu-text">Dashboard</span>
        </a>
        <a href="empjoblist.php" class="menu-item <?= $currentPage == 'empjoblist.php' ? 'active' : '' ?>">
            <i class="fas fa-briefcase"></i>
            <span class="menu-text">Job Listings</span>
        </a>
        <a href="empapplicants.php" class="menu-item <?= $currentPage == 'empapplicants.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i>
            <span class="menu-text">Applicants</span>
        </a>
        <a href="empnotifications.php" class="menu-item <?= $currentPage == 'empnotifications.php' ? 'active' : '' ?>">
            <i class="fas fa-bell"></i>
            <span class="menu-text">Notifications</span>
        </a>
        <a href="empprofile.php" class="menu-item <?= $currentPage == 'empprofile.php' ? 'active' : '' ?>">
            <i class="fas fa-building"></i>
            <span class="menu-text">Company Profile</span>
        </a>
        <a href="empsettings.php" class="menu-item <?= $currentPage == 'empsettings.php' ? 'active' : '' ?>">
            <i class="fas fa-cog"></i>
            <span class="menu-text">Settings</span>
        </a>
    </div>
    <div class="toggle-btn" id="toggle-btn">
        <i class="fas fa-chevron-left" id="toggle-icon"></i>
    </div>
</div>
