<?php
session_start();
require_once '../config/db_connect.php';
require_once '../admin_check.php';
checkAdmin();

// Fetch article data
if (isset($_GET['id'])) {
    $article_id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
        $stmt->execute([$article_id]);
        $article = $stmt->fetch();
        
        if (!$article) {
            $_SESSION['error_message'] = "Article not found!";
            header("Location: manage_articles.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header("Location: manage_articles.php");
        exit();
    }
} else {
    header("Location: manage_articles.php");
    exit();
}

// Fetch categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
    $status = isset($_POST['status']) ? 1 : 0;
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Handle image upload if new image is selected
        $image_url = $article['image_url']; // Keep existing image by default
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                // Delete old image if exists
                if (!empty($article['image_url']) && file_exists('../' . $article['image_url'])) {
                    unlink('../' . $article['image_url']);
                }
                
                // Upload new image
                $new_filename = uniqid() . '.' . $filetype;
                $upload_path = '../uploads/' . $new_filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image_url = 'uploads/' . $new_filename;
                }
            }
        }
        
        // Update article
        $sql = "UPDATE articles SET 
                title = ?, 
                content = ?, 
                image_url = ?,
                category_id = ?,
                status = ?,
                updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
                
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$title, $content, $image_url, $category_id, $status, $article_id])) {
            $pdo->commit();
            $_SESSION['success_message'] = "Article updated successfully!";
            header("Location: manage_articles.php");
            exit();
        }
    } catch(PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error updating article: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Article - Admin Panel</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.tiny.cloud/1/di96k5h9x0oaxtzb4kbgvdohcv3b5r56j6qo5r4ue9d7bgih/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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
        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .current-image {
            max-width: 200px;
            margin-bottom: 10px;
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
                    <span class="navbar-brand mb-0 h1">Edit Article</span>
                </nav>

                <div class="form-container">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label><strong>Article Title</strong></label>
                            <input type="text" name="title" class="form-control" 
                                   value="<?php echo htmlspecialchars($article['title']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label><strong>Category</strong></label>
                            <select name="category_id" class="form-control">
                                <option value="">Select Category</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo ($article['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><strong>Featured Image</strong></label>
                            <?php if($article['image_url']): ?>
                                <div>
                                    <img src="../<?php echo htmlspecialchars($article['image_url']); ?>" 
                                         class="current-image" alt="Current image">
                                </div>
                            <?php endif; ?>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="image" name="image" accept="image/*">
                                <label class="custom-file-label" for="image">Choose new image</label>
                            </div>
                            <small class="form-text text-muted">Leave empty to keep current image</small>
                        </div>

                        <div class="form-group">
                            <label><strong>Content</strong></label>
                            <textarea name="content" id="content" class="form-control">
                                <?php echo htmlspecialchars($article['content']); ?>
                            </textarea>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="status" name="status"
                                       <?php echo $article['status'] ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="status">Published</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Save Changes
                            </button>
                            <a href="manage_articles.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>Back to Articles
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        tinymce.init({
            selector: '#content',
            height: 500,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help'
        });

        // Update file input label with selected filename
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });
    </script>
</body>
</html> 