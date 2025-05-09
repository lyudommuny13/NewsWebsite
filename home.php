<?php
session_start();
require_once 'config/db_connect.php';

// Fetch featured articles (latest 3)
$featured_stmt = $pdo->query("SELECT articles.*, categories.name as category_name 
                     FROM articles 
                     LEFT JOIN categories ON articles.category_id = categories.id 
                     WHERE articles.status = 1 
                     ORDER BY created_at DESC LIMIT 3");
$featured_articles = $featured_stmt->fetchAll();

// Fetch remaining articles
$articles_stmt = $pdo->query("SELECT articles.*, categories.name as category_name 
                     FROM articles 
                     LEFT JOIN categories ON articles.category_id = categories.id 
                     WHERE articles.status = 1 
                     ORDER BY created_at DESC LIMIT 3, 100");
$articles = $articles_stmt->fetchAll();

// Fetch categories for filter
$categories_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $categories_stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>NewsPortal - Your Source for Latest News</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            height: 600px;
            overflow: hidden;
            margin-bottom: 3rem;
        }
        .hero-slide {
            height: 600px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.7));
            display: flex;
            align-items: center;
            padding: 2rem;
        }
        .hero-content {
            color: white;
            max-width: 800px;
            animation: fadeInUp 0.5s ease-out;
        }
        .hero-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        /* Article Cards */
        .article-card {
            transition: all 0.3s ease;
            margin-bottom: 30px;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .article-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        .article-image {
            height: 250px;
            object-fit: cover;
            transition: all 0.3s ease;
        }
        .article-card:hover .article-image {
            transform: scale(1.05);
        }
        .category-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(255,255,255,0.9);
            color: #333;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Filter Section */
        .filter-section {
            background: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .filter-btn {
            margin: 0.25rem;
            border-radius: 20px;
            padding: 8px 20px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .filter-btn:hover, .filter-btn.active {
            background: #007bff;
            color: white;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-section {
                height: 400px;
            }
            .hero-slide {
                height: 400px;
            }
            .hero-title {
                font-size: 1.8rem;
            }
            .article-image {
                height: 200px;
            }
        }

        @media (max-width: 576px) {
            .hero-section {
                height: 300px;
            }
            .hero-slide {
                height: 300px;
            }
            .hero-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section with Featured Articles -->
    <div id="heroCarousel" class="carousel slide hero-section" data-ride="carousel">
        <div class="carousel-inner">
            <?php foreach($featured_articles as $index => $article): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <div class="hero-slide" style="background-image: url('<?php echo htmlspecialchars($article['image_url'] ?: 'https://images.pexels.com/photos/518543/pexels-photo-518543.jpeg'); ?>');">
                        <div class="hero-overlay">
                            <div class="hero-content">
                                <?php if($article['category_name']): ?>
                                    <span class="badge badge-primary mb-2"><?php echo htmlspecialchars($article['category_name']); ?></span>
                                <?php endif; ?>
                                <h1 class="hero-title"><?php echo htmlspecialchars($article['title']); ?></h1>
                                <p class="lead"><?php echo substr(strip_tags($article['content']), 0, 150) . '...'; ?></p>
                                <a href="article.php?id=<?php echo $article['id']; ?>" class="btn btn-primary btn-lg">Read More</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <a class="carousel-control-prev" href="#heroCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#heroCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>

    <!-- Category Filter -->
    <div class="filter-section">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-center">
                <button class="btn filter-btn active" data-category="all">All</button>
                <?php foreach($categories as $category): ?>
                    <button class="btn filter-btn" data-category="<?php echo htmlspecialchars($category['name']); ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Articles Grid -->
    <div class="container">
        <div class="row" id="articlesGrid">
            <?php foreach($articles as $article): ?>
                <div class="col-md-4 article-item" data-category="<?php echo htmlspecialchars($article['category_name']); ?>">
                    <div class="card article-card">
                        <div class="position-relative overflow-hidden">
                            <img src="<?php echo htmlspecialchars($article['image_url'] ?: 'https://images.pexels.com/photos/518543/pexels-photo-518543.jpeg'); ?>" 
                                 class="card-img-top article-image" alt="Article image">
                            <?php if($article['category_name']): ?>
                                <span class="category-badge">
                                    <?php echo htmlspecialchars($article['category_name']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($article['title']); ?></h5>
                            <p class="card-text text-muted">
                                <small>
                                    <i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($article['author']); ?>
                                    <i class="fas fa-clock ml-2 mr-2"></i><?php echo date('M d, Y', strtotime($article['created_at'])); ?>
                                </small>
                            </p>
                            <p class="card-text">
                                <?php echo substr(strip_tags($article['content']), 0, 100) . '...'; ?>
                            </p>
                            <a href="article.php?id=<?php echo $article['id']; ?>" class="btn btn-outline-primary">
                                Read More <i class="fas fa-arrow-right ml-1"></i>
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
    
    <script>
        // Category filter functionality
        $(document).ready(function() {
            $('.filter-btn').click(function() {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                
                var category = $(this).data('category');
                
                if (category === 'all') {
                    $('.article-item').fadeIn();
                } else {
                    $('.article-item').hide();
                    $('.article-item[data-category="' + category + '"]').fadeIn();
                }
            });

            // Initialize carousel
            $('.carousel').carousel({
                interval: 5000
            });
        });
    </script>
</body>
</html>