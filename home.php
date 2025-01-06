<?php
session_start();
require_once 'config/db_connect.php';

// Fetch articles
$stmt = $pdo->query("SELECT articles.*, categories.name as category_name 
                     FROM articles 
                     LEFT JOIN categories ON articles.category_id = categories.id 
                     WHERE articles.status = 1 
                     ORDER BY created_at DESC");
$articles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>NewsPortal - Home</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .article-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .article-card:hover {
            transform: translateY(-5px);
        }
        .article-image {
            height: 200px;
            object-fit: cover;
        }
        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <?php foreach($articles as $article): ?>
                <div class="col-md-4">
                    <div class="card article-card shadow-sm">
                        <?php if($article['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($article['image_url']); ?>" 
                                 class="card-img-top article-image" alt="Article image">
                        <?php endif; ?>
                        <?php if($article['category_name']): ?>
                            <span class="badge badge-primary category-badge">
                                <?php echo htmlspecialchars($article['category_name']); ?>
                            </span>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($article['title']); ?></h5>
                            <p class="card-text text-muted">
                                <small>
                                    <i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($article['author']); ?>
                                    <i class="fas fa-clock ml-2 mr-2"></i><?php echo date('M d, Y', strtotime($article['created_at'])); ?>
                                </small>
                            </p>
                            <p class="card-text">
                                <?php echo substr(strip_tags($article['content']), 0, 150) . '...'; ?>
                            </p>
                            <a href="article.php?id=<?php echo $article['id']; ?>" class="btn btn-primary">
                                Read More
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 