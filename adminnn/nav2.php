<?php
session_start();
$_user_role = $_SESSION['user_role'] ?? '';
?>

<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="./index.php" class="b-brand text-primary">
                <img src="https://sarsspl.com/assets/images/logo/headlogo.png" 
                     class="img-fluid logo-lg" alt="logo" />
            </a>
        </div>

        <div class="navbar-content">
            <ul class="pc-navbar">

                <li class="pc-item pc-caption">
                    <label>Navigation</label>
                </li>

                <!-- ================= ADMIN ROLE ================= -->
                <?php if($_user_role == 1) { ?>   <!-- 1 = Admin -->

                    <li class="pc-item">
                        <a href="./index.php" class="pc-link">
                            <span class="pc-micon"><i class="ph ph-desktop"></i></span>
                            <span class="pc-mtext">Dashboard</span>
                        </a>
                    </li>

                    <li class="pc-item pc-hasmenu">
                        <a href="#!" class="pc-link">
                            <span class="pc-micon">
                                <i class="ph ph-house-line"></i>
                            </span>
                            <span class="pc-mtext">Admin</span>
                            <span class="pc-arrow">
                                <i class="ti ti-chevron-right"></i>
                            </span>
                        </a>

                        <ul class="pc-submenu">
                            <li class="pc-item">
                                <a class="pc-link" href="./users.php">Users</a>
                            </li>
                            <li class="pc-item">
                                <a class="pc-link" href="./user_location_update.php">
                                    User Location Update
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="pc-item">
                        <a href="./add_coordinator.php" class="pc-link">
                            <span class="pc-micon"><i class="ph ph-desktop"></i></span>
                            <span class="pc-mtext">Add Coordinator</span>
                        </a>
                    </li>

                    <li class="pc-item">
                        <a href="./otp_request_category.php" class="pc-link">
                            <span class="pc-micon"><i class="ph ph-desktop"></i></span>
                            <span class="pc-mtext">OTP Request Category</span>
                        </a>
                    </li>

                    <li class="pc-item">
                        <a href="./today_alerts.php" class="pc-link">
                            <span class="pc-micon"><i class="ph ph-bell"></i></span>
                            <span class="pc-mtext">Today Alerts</span>
                        </a>
                    </li>

                    <li class="pc-item">
                        <a href="./alerts.php" class="pc-link">
                            <span class="pc-micon"><i class="ph ph-bell"></i></span>
                            <span class="pc-mtext">All Alerts</span>
                        </a>
                    </li>

                    <li class="pc-item">
                        <a href="./offlineotp.php" class="pc-link">
                            <span class="pc-micon"><i class="ph ph-desktop"></i></span>
                            <span class="pc-mtext">Offline OTP</span>
                        </a>
                    </li>

                    <li class="pc-item">
                        <a href="./panel.php" class="pc-link">
                            <span class="pc-micon"><i class="ph ph-desktop"></i></span>
                            <span class="pc-mtext">Panel</span>
                        </a>
                    </li>

                    <li class="pc-item">
                        <a href="./set_panel.php" class="pc-link">
                            <span class="pc-micon"><i class="ph ph-desktop"></i></span>
                            <span class="pc-mtext">Set Panel</span>
                        </a>
                    </li>

                    <li class="pc-item">
                        <a href="./assign_coordinator.php" class="pc-link">
                            <span class="pc-micon"><i class="ph ph-desktop"></i></span>
                            <span class="pc-mtext">Assign Coordinator</span>
                        </a>
                    </li>

                    <li class="pc-item">
                        <a href="./user_security_question.php" class="pc-link">
                            <span class="pc-micon"><i class="ph ph-desktop"></i></span>
                            <span class="pc-mtext">Security Question</span>
                        </a>
                    </li>

                    <li class="pc-item">
                        <a href="./branch.php" class="pc-link">
                            <span class="pc-micon"><i class="ph ph-desktop"></i></span>
                            <span class="pc-mtext">Branch</span>
                        </a>
                    </li>

                    <li class="pc-item">
                        <a href="./user_lead.php" class="pc-link">
                            <span class="pc-micon"><i class="ph ph-desktop"></i></span>
                            <span class="pc-mtext">User Lead</span>
                        </a>
                    </li>

                    <li class="pc-item">
                        <a href="./signup_request.php" class="pc-link">
                            <span class="pc-micon"><i class="ph ph-desktop"></i></span>
                            <span class="pc-mtext">Signup Request</span>
                        </a>
                    </li>

                <?php } ?>


                <!-- ================= USER ROLE 7 ================= -->
                <?php if($_user_role == 7) { ?>

                    <li class="pc-item">
                        <a href="./index.php" class="pc-link">
                            <span class="pc-micon"><i class="ph ph-desktop"></i></span>
                            <span class="pc-mtext">Dashboard</span>
                        </a>
                    </li>

                    <li class="pc-item">
                        <a href="./today_alerts_new.php" class="pc-link">
                            <span class="pc-micon"><i class="ph ph-bell"></i></span>
                            <span class="pc-mtext">Today Alerts</span>
                        </a>
                    </li>

                <?php } ?>

            </ul>
        </div>
    </div>
</nav>
