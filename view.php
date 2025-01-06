<?php
require_once 'config/db_connect.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($article['title']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1><?php echo htmlspecialchars($article['title']); ?></h1>
        <?php if($article['image_url']): ?>
            <img src="<?php echo $article['image_url']; ?>" class="img-fluid mb-3" alt="Article Image">
        <?php endif; ?>
        <p class="text-muted">By <?php echo htmlspecialchars($article['author']); ?> on <?php echo date('F j, Y', strtotime($article['created_at'])); ?></p>
        <div class="article-content">
            <?php echo nl2br(htmlspecialchars($article['content'])); ?>
        </div>
        <div class="mt-4">
            <a href="index.php" class="btn btn-secondary">Back to Home</a>
            <a href="edit.php?id=<?php echo $article['id']; ?>" class="btn btn-warning">Edit</a>
            <a href="delete.php?id=<?php echo $article['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
        </div>
    </div>
</body>
</html> 