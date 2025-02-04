<?php
function validateRequired($value, $fieldName)
{
    if (empty($value)) {
        throw new Exception("Field {$fieldName} harus diisi!");
    }
    return true;
}

function validateEmail($email)
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Format email tidak valid!");
    }
    return true;
}

function validateUsername($username)
{
    if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
        throw new Exception("Username hanya boleh mengandung huruf, angka, dan underscore (4-20 karakter)!");
    }
    return true;
}

function validatePassword($password)
{
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
        throw new Exception("Password minimal 8 karakter dan harus mengandung huruf dan angka!");
    }
    return true;
}

function validateFile($file, $allowedTypes, $maxSize)
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Error saat upload file!");
    }

    if ($file['size'] > $maxSize) {
        throw new Exception("Ukuran file terlalu besar! Maksimal " . ($maxSize / 1024 / 1024) . "MB");
    }

    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception("Tipe file tidak diizinkan!");
    }

    return true;
}
