<?php
session_start();
require_once '../config/db_connect.php';
require_once '../admin_check.php';
checkAdmin();

if (isset($_GET['id'])) {
    $article_id = $_GET['id'];
    
    try {
        // First get the article image if exists
        $stmt = $pdo->prepare("SELECT image_url FROM articles WHERE id = ?");
        $stmt->execute([$article_id]);
        $article = $stmt->fetch();
        
        // Delete the article
        $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
        if ($stmt->execute([$article_id])) {
            // Delete the image file if exists
            if (!empty($article['image_url'])) {
                $image_path = "../" . $article['image_url'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            $_SESSION['success_message'] = "Article deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete article.";
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
    
    header("Location: manage_articles.php");
    exit();
} else {
    header("Location: manage_articles.php");
    exit();
}
?> 