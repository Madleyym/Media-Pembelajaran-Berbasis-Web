<?php
function checkUserStatus($conn, $userId) {
    $stmt = $conn->prepare("
        SELECT status 
        FROM users 
        WHERE id = ? 
        AND status = 'active'
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    if (!$result) {
        session_destroy();
        $_SESSION['error'] = 'Akun anda tidak aktif. Silahkan hubungi admin.';
        header("Location: " . BASE_URL . "/index.php?page=login");
        exit();
    }
}