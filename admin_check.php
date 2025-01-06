<?php
function checkAdmin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        header("Location: ../login.php");
        exit();
    }
}
?> 