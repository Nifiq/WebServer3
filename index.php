<?php
// Функция поиска недопустимых символов
function getInvalidChars($string, $allowedPattern) {
    $invalid = preg_replace('/[' . $allowedPattern . ']/u', '', $string);
    if ($invalid === '') return [];
    return array_unique(mb_str_split($invalid));
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

$errors = []; // ассоциативный массив ошибок

// ---------- Валидация ----------
// 1. ФИО
if (empty($name)) {
    $errors['name'] = 'ФИО обязательно для заполнения.';
} else {
    $allowedNamePattern = 'a-zA-Zа-яА-ЯёЁ\s\-’\'';
    $invalidChars = getInvalidChars($name, $allowedNamePattern);
    if (!empty($invalidChars)) {
        $errors['name'] = 'ФИО содержит недопустимые символы: ' . implode(', ', array_map('htmlspecialchars', $invalidChars));
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
        $errors['name'] = 'ФИО должно содержать от 2 до 150 символов.';
    }
}

// 2. Телефон (необязательный)
if (!empty($phone)) {
    $allowedPhonePattern = '0-9+\-\(\)\s';
    $invalidChars = getInvalidChars($phone, $allowedPhonePattern);
    if (!empty($invalidChars)) {
        $errors['phone'] = 'Телефон содержит недопустимые символы: ' . implode(', ', array_map('htmlspecialchars', $invalidChars));
    }
}

// 3. Email
if (empty($email)) {
    $errors['email'] = 'Email обязателен для заполнения.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Некорректный email.';
} else {
    // Дополнительная проверка символов в email (не обязательна, но можно)
    $allowedEmailPattern = 'a-zA-Z0-9.@_\-+';
    $invalidChars = getInvalidChars($email, $allowedEmailPattern);
    if (!empty($invalidChars)) {
        $errors['email'] = 'Email содержит недопустимые символы: ' . implode(', ', array_map('htmlspecialchars', $invalidChars));
    }
}

// 4. Дата рождения
if (!empty($birthdate)) {
    $date = DateTime::createFromFormat('Y-m-d', $birthdate);
    if (!$date || $date->format('Y-m-d') !== $birthdate) {
        $errors['birthdate'] = 'Неверный формат даты. Используйте ГГГГ-ММ-ДД.';
    }
}

// 5. Пол
if (!in_array($gender, ['male', 'female'], true)) {
    $errors['gender'] = 'Выберите пол.';
}

// 6. Биография
if (!empty($bio)) {
    $allowedBioPattern = 'a-zA-Zа-яА-ЯёЁ0-9\s\.,!?;:()\-—"\'';
    $invalidChars = getInvalidChars($bio, $allowedBioPattern);
    if (!empty($invalidChars)) {
        $errors['bio'] = 'Биография содержит недопустимые символы: ' . implode(', ', array_map('htmlspecialchars', $invalidChars));
    }
}

// 7. Языки программирования
if (empty($languages) || !is_array($languages)) {
    $errors['languages'] = 'Выберите хотя бы один язык.';
} else {
    // Проверим, что выбранные языки существуют в справочнике (на всякий случай)
    $validLanguageIds = [1,2,3,4,5]; // для примера
    foreach ($languages as $langId) {
        if (!in_array($langId, $validLanguageIds)) {
            $errors['languages'] = 'Выбран недопустимый язык.';
            break;
        }
    }
}

// 8. Согласие
if (!$contract) {
    $errors['contract'] = 'Необходимо дать согласие на обработку данных.';
}

// ---------- Если есть ошибки ----------
if (!empty($errors)) {
    // Сохраняем введённые данные в cookies (сессионные)
    $formData = [
        'name'      => $name,
        'phone'     => $phone,
        'email'     => $email,
        'birthdate' => $birthdate,
        'gender'    => $gender,
        'bio'       => $bio,
        'contract'  => $contract,
        'languages' => $languages
    ];
    setcookie('form_data', serialize($formData), 0, '/');
    setcookie('form_errors', serialize($errors), 0, '/');

    header('Location: form.php');
    exit;
}

// ---------- Сохранение в БД (PDO) ----------
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
    die("Ошибка подключения к базе: " . $e->getMessage());
}

try {
    // Вставка в основную таблицу applications
    $stmt = $db->prepare("INSERT INTO applications 
        (name, phone, email, birthdate, gender, bio, contract) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $phone, $email, $birthdate, $gender, $bio, $contract]);
    $app_id = $db->lastInsertId();

    // Вставка языков
    $stmt2 = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
    foreach ($languages as $langId) {
        $stmt2->execute([$app_id, (int)$langId]);
    }

    // Удаляем cookies
    setcookie('form_data', '', time() - 3600, '/');
    setcookie('form_errors', '', time() - 3600, '/');

    header('Location: success.php');
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    die("Ошибка при сохранении: " . $e->getMessage());
}