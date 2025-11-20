<?php
// This file redirects to dashboard for instructors
// The courses are already shown on the dashboard
require_once __DIR__ . '/../../config/config.php';
requireRole(ROLE_INSTRUCTOR);
redirect('views/instructor/dashboard.php');
?>
