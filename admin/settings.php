<?php
session_start();
require_once '../config/db_connect.php';
require_once '../admin_check.php';
checkAdmin();

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        foreach ($_POST['settings'] as $key => $value) {
            // Handle file uploads
            if (isset($_FILES['settings']['name'][$key]) && $_FILES['settings']['error'][$key] == 0) {
                $file = $_FILES['settings']['tmp_name'][$key];
                $filename = $_FILES['settings']['name'][$key];
                $upload_dir = '../uploads/';
                $new_filename = uniqid() . '_' . $filename;
                
                if (move_uploaded_file($file, $upload_dir . $new_filename)) {
                    $value = 'uploads/' . $new_filename;
                }
            }
            
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
        }
        
        $pdo->commit();
        $_SESSION['success_message'] = "Settings updated successfully!";
    } catch(PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error updating settings: " . $e->getMessage();
    }
    
    header("Location: settings.php");
    exit();
}

// Fetch all settings grouped by setting_group
$stmt = $pdo->query("SELECT * FROM settings ORDER BY setting_group, id");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_group']][] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Website Settings - Admin Panel</title>
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
        .settings-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .nav-pills .nav-link.active {
            background-color: #2c3e50;
        }
        .settings-preview {
            max-width: 150px;
            max-height: 150px;
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
                        <a class="nav-link active" href="settings.php">
                            <i class="fas fa-cogs mr-2"></i>Settings
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
                    <span class="navbar-brand mb-0 h1">Website Settings</span>
                </nav>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                        ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
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
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="settings-container">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="nav flex-column nav-pills" role="tablist">
                                <?php 
                                $first = true;
                                foreach ($settings as $group => $group_settings): 
                                ?>
                                    <a class="nav-link <?php echo $first ? 'active' : ''; ?>" 
                                       data-toggle="pill" href="#<?php echo $group; ?>">
                                        <?php echo ucfirst($group); ?> Settings
                                    </a>
                                <?php 
                                $first = false;
                                endforeach; 
                                ?>
                            </div>
                        </div>
                        
                        <div class="col-md-9">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="tab-content">
                                    <?php 
                                    $first = true;
                                    foreach ($settings as $group => $group_settings): 
                                    ?>
                                        <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?>" 
                                             id="<?php echo $group; ?>">
                                            <h4 class="mb-4"><?php echo ucfirst($group); ?> Settings</h4>
                                            
                                            <?php foreach ($group_settings as $setting): ?>
                                                <div class="form-group">
                                                    <label>
                                                        <strong><?php echo htmlspecialchars($setting['label']); ?></strong>
                                                        <?php if ($setting['description']): ?>
                                                            <small class="text-muted d-block">
                                                                <?php echo htmlspecialchars($setting['description']); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </label>
                                                    
                                                    <?php switch($setting['setting_type']): 
                                                        case 'textarea': ?>
                                                            <textarea name="settings[<?php echo $setting['setting_key']; ?>]" 
                                                                      class="form-control" rows="3"><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>
                                                            <?php break; ?>
                                                            
                                                        <?php case 'boolean': ?>
                                                            <div class="custom-control custom-switch">
                                                                <input type="checkbox" 
                                                                       class="custom-control-input" 
                                                                       id="<?php echo $setting['setting_key']; ?>"
                                                                       name="settings[<?php echo $setting['setting_key']; ?>]"
                                                                       value="1"
                                                                       <?php echo $setting['setting_value'] ? 'checked' : ''; ?>>
                                                                <label class="custom-control-label" 
                                                                       for="<?php echo $setting['setting_key']; ?>">
                                                                    Enable
                                                                </label>
                                                            </div>
                                                            <?php break; ?>
                                                            
                                                        <?php case 'file': ?>
                                                            <div class="custom-file">
                                                                <input type="file" 
                                                                       class="custom-file-input" 
                                                                       name="settings[<?php echo $setting['setting_key']; ?>]"
                                                                       id="<?php echo $setting['setting_key']; ?>">
                                                                <label class="custom-file-label" 
                                                                       for="<?php echo $setting['setting_key']; ?>">
                                                                    Choose file
                                                                </label>
                                                            </div>
                                                            <?php if ($setting['setting_value']): ?>
                                                                <img src="../<?php echo htmlspecialchars($setting['setting_value']); ?>" 
                                                                     class="mt-2 settings-preview" alt="Preview">
                                                            <?php endif; ?>
                                                            <?php break; ?>
                                                            
                                                        <?php case 'color': ?>
                                                            <input type="color" 
                                                                   class="form-control" 
                                                                   name="settings[<?php echo $setting['setting_key']; ?>]"
                                                                   value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                                            <?php break; ?>
                                                            
                                                        <?php case 'password': ?>
                                                            <input type="password" 
                                                                   class="form-control" 
                                                                   name="settings[<?php echo $setting['setting_key']; ?>]"
                                                                   value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                                            <?php break; ?>
                                                            
                                                        <?php default: ?>
                                                            <input type="<?php echo $setting['setting_type']; ?>" 
                                                                   class="form-control" 
                                                                   name="settings[<?php echo $setting['setting_key']; ?>]"
                                                                   value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                                    <?php endswitch; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php 
                                    $first = false;
                                    endforeach; 
                                    ?>
                                </div>
                                
                                <div class="form-group mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-2"></i>Save Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Update file input label with selected filename
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });
    </script>
</body>
</html> 