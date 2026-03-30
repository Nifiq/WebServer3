<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Подключение к БД
    try {
        $db = new PDO(
            'mysql:host=localhost;dbname=forms_db;charset=utf8mb4',
            'root',
            'Nifi753159Q*',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
            ]
        );
    } catch (PDOException $e) {
        http_response_code(500);
        die("Ошибка подключения: " . $e->getMessage());
    }

    // Получение данных
    $name      = trim($_POST['name'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $birthdate = $_POST['birthdate'] ?? '';
    $gender    = $_POST['gender'] ?? '';
    $bio       = trim($_POST['bio'] ?? '');
    $contract  = isset($_POST['contract']) ? 1 : 0;
    $languages = $_POST['languages'] ?? [];

    $errors = [];

    // ВАЛИДАЦИЯ (regex)
    if (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s]{2,150}$/u", $name)) {
        $errors['name'] = 'Только буквы и пробелы (2-150 символов)';
    }

    if (!preg_match("/^\+?[0-9\s\-]{7,20}$/", $phone)) {
        $errors['phone'] = 'Допустимы цифры, пробелы, + и -';
    }

    if (!preg_match("/^[^@\s]+@[^@\s]+\.[^@\s]+$/", $email)) {
        $errors['email'] = 'Некорректный email';
    }

    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $birthdate)) {
        $errors['birthdate'] = 'Некорректная дата';
    }

    if (!in_array($gender, ['male', 'female'], true)) {
        $errors['gender'] = 'Выберите пол';
    }

    if (empty($languages)) {
        $errors['languages'] = 'Выберите хотя бы один язык';
    }

    if (!$contract) {
        $errors['contract'] = 'Необходимо согласие';
    }

    // ЕСЛИ ЕСТЬ ОШИБКИ
    if (!empty($errors)) {
        setcookie('errors', json_encode($errors), 0, '/');
        setcookie('old', json_encode($_POST), 0, '/');

        header('Location: form.php');
        exit();
    }

    // СОХРАНЕНИЕ В БД
    try {
        $stmt = $db->prepare("INSERT INTO applications 
            (name, phone, email, birthdate, gender, bio, contract) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([$name, $phone, $email, $birthdate, $gender, $bio, $contract]);
        $app_id = $db->lastInsertId();

        $stmt2 = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");

        foreach ($languages as $lang) {
            $stmt2->execute([$app_id, (int)$lang]);
        }

        // Сохраняем данные на 1 год
        setcookie('values', json_encode($_POST), time() + 365*24*60*60, '/');
        setcookie('success', '1', time() + 5, '/');
        setcookie('success_id', $app_id, time() + 10, '/');

        header('Location: form.php');
        exit();

    } catch (PDOException $e) {
        http_response_code(500);
        die("Ошибка сохранения: " . $e->getMessage());
    }
}