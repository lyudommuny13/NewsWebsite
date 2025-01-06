<?php
session_start();
require_once '../config/db_connect.php';
require_once '../admin_check.php';
checkAdmin();

// Get statistics
$totalArticles = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalViews = $pdo->query("SELECT SUM(views) FROM articles")->fetchColumn() ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - NewsPortal</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #2c3e50;
            color: white;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            display: block;
        }
        .sidebar a:hover {
            background-color: #34495e;
        }
        .main-content {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            background-color: white;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .quick-action-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .quick-action-card:hover {
            transform: translateY(-5px);
        }
        .quick-action-btn {
            display: block;
            width: 100%;
            padding: 15px;
            margin-bottom: 10px;
            text-align: left;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .welcome-card {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .border-left-primary {
            border-left: .25rem solid #4e73df!important;
        }
        .border-left-success {
            border-left: .25rem solid #1cc88a!important;
        }
        .border-left-info {
            border-left: .25rem solid #36b9cc!important;
        }
        .border-left-warning {
            border-left: .25rem solid #f6c23e!important;
        }
        .text-gray-300 {
            color: #dddfeb!important;
        }
        .text-gray-800 {
            color: #5a5c69!important;
        }
        .card {
            transition: transform .2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <h4 class="text-center mb-4">Admin Panel</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_articles.php">
                            <i class="fas fa-newspaper mr-2"></i>Articles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_users.php">
                            <i class="fas fa-users mr-2"></i>Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../home.php">
                            <i class="fas fa-home mr-2"></i>View Site
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                        ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Welcome Card -->
                <div class="welcome-card">
                    <h2><i class="fas fa-user-circle mr-2"></i>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
                    <p class="mb-0">Welcome to your admin dashboard. Here's an overview of your website.</p>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <!-- Total Articles Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Articles</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalArticles; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-newspaper fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Users Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Users</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalUsers; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Categories Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Categories</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalCategories; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-folder fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Views Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Total Views</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalViews; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-eye fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="quick-action-card">
                            <h5 class="mb-4"><i class="fas fa-bolt mr-2"></i>Quick Actions</h5>
                            <a href="create_article.php" class="btn btn-primary quick-action-btn">
                                <i class="fas fa-plus-circle mr-2"></i>Create New Article
                            </a>
                            <a href="manage_articles.php" class="btn btn-success quick-action-btn">
                                <i class="fas fa-newspaper mr-2"></i>Manage Articles
                            </a>
                            <a href="manage_users.php" class="btn btn-info quick-action-btn">
                                <i class="fas fa-users-cog mr-2"></i>Manage Users
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="quick-action-card">
                            <h5 class="mb-4"><i class="fas fa-cog mr-2"></i>System Info</h5>
                            <div class="list-group">
                                <div class="list-group-item">
                                    <i class="fas fa-server mr-2"></i>
                                    PHP Version: <?php echo phpversion(); ?>
                                </div>
                                <div class="list-group-item">
                                    <i class="fas fa-database mr-2"></i>
                                    Database: MySQL
                                </div>
                                <div class="list-group-item">
                                    <i class="fas fa-clock mr-2"></i>
                                    Server Time: <?php echo date('Y-m-d H:i:s'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>