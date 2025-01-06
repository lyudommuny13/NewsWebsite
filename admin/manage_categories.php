<?php
session_start();
require_once '../config/db_connect.php';
require_once '../admin_check.php';
checkAdmin();

// Handle category creation
if (isset($_POST['add_category'])) {
    $name = trim($_POST['category_name']);
    $slug = strtolower(str_replace(' ', '-', $name));
    
    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        if ($stmt->execute([$name, $slug])) {
            $_SESSION['success_message'] = "Category added successfully!";
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error adding category: " . $e->getMessage();
    }
    header("Location: manage_categories.php");
    exit();
}

// Handle category deletion
if (isset($_POST['delete_category'])) {
    $category_id = $_POST['category_id'];
    try {
        // First, update articles to remove this category
        $stmt = $pdo->prepare("UPDATE articles SET category_id = NULL WHERE category_id = ?");
        $stmt->execute([$category_id]);
        
        // Then delete the category
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        if ($stmt->execute([$category_id])) {
            $_SESSION['success_message'] = "Category deleted successfully!";
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error deleting category: " . $e->getMessage();
    }
    header("Location: manage_categories.php");
    exit();
}

// Handle category update
if (isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $name = trim($_POST['category_name']);
    $slug = strtolower(str_replace(' ', '-', $name));
    
    try {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
        if ($stmt->execute([$name, $slug, $category_id])) {
            $_SESSION['success_message'] = "Category updated successfully!";
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error updating category: " . $e->getMessage();
    }
    header("Location: manage_categories.php");
    exit();
}

// Fetch all categories with article count
$stmt = $pdo->query("SELECT categories.*, COUNT(articles.id) as article_count 
                     FROM categories 
                     LEFT JOIN articles ON categories.id = articles.category_id 
                     GROUP BY categories.id 
                     ORDER BY categories.name ASC");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories - Admin Panel</title>
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
        .category-card {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .category-list {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
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
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_articles.php">
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
                <nav class="navbar navbar-light bg-white mb-4 shadow-sm">
                    <span class="navbar-brand mb-0 h1">Manage Categories</span>
                    <div>
                        <a href="manage_articles.php" class="btn btn-secondary mr-2">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Articles
                        </a>
                    </div>
                </nav>

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

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                        ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Add Category Form -->
                    <div class="col-md-4">
                        <div class="category-card">
                            <h5 class="mb-4"><i class="fas fa-plus-circle mr-2"></i>Add New Category</h5>
                            <form action="" method="POST">
                                <div class="form-group">
                                    <label for="category_name">Category Name</label>
                                    <input type="text" class="form-control" id="category_name" name="category_name" required>
                                </div>
                                <button type="submit" name="add_category" class="btn btn-primary">
                                    <i class="fas fa-plus mr-2"></i>Add Category
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Categories List -->
                    <div class="col-md-8">
                        <div class="category-list">
                            <h5 class="mb-4"><i class="fas fa-list mr-2"></i>Categories</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Category Name</th>
                                            <th>Slug</th>
                                            <th>Articles</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($categories as $category): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                                            <td><?php echo htmlspecialchars($category['slug']); ?></td>
                                            <td>
                                                <span class="badge badge-primary">
                                                    <?php echo $category['article_count']; ?> articles
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-warning btn-sm" data-toggle="modal" 
                                                        data-target="#editModal<?php echo $category['id']; ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <form action="" method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Are you sure? Articles in this category will be uncategorized.');">
                                                    <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                                    <button type="submit" name="delete_category" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?php echo $category['id']; ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Category</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <form action="" method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                                            <div class="form-group">
                                                                <label>Category Name</label>
                                                                <input type="text" class="form-control" name="category_name" 
                                                                       value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                            <button type="submit" name="update_category" class="btn btn-primary">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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