<?php
// Читаем сохранённые данные и ошибки из cookies
$formData = [];
if (isset($_COOKIE['form_data'])) {
    $formData = unserialize($_COOKIE['form_data']);
    if (!is_array($formData)) $formData = [];
}
$formErrors = [];
if (isset($_COOKIE['form_errors'])) {
    $formErrors = unserialize($_COOKIE['form_errors']);
    if (!is_array($formErrors)) $formErrors = [];
}

// Значения полей по умолчанию
$name      = $formData['name'] ?? '';
$phone     = $formData['phone'] ?? '';
$email     = $formData['email'] ?? '';
$birthdate = $formData['birthdate'] ?? '';
$gender    = $formData['gender'] ?? '';
$bio       = $formData['bio'] ?? '';
$contract  = $formData['contract'] ?? false;
$languages = $formData['languages'] ?? [];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрационная форма</title>
    <style>
        .error-field {
            border: 2px solid red;
            background-color: #ffe6e6;
        }
        .error-message {
            color: red;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .allowed-chars {
            font-size: 0.8em;
            color: #666;
            margin-top: 3px;
        }
        .field-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="date"],
        textarea,
        select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .checkbox-group {
            margin-top: 5px;
        }
        .checkbox-group label {
            display: inline-block;
            font-weight: normal;
            margin-right: 10px;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h2>Регистрационная форма</h2>

    <form action="process.php" method="post">
        <!-- ФИО -->
        <div class="field-group">
            <label for="name">ФИО *</label>
            <input type="text" name="name" id="name"
                   value="<?= htmlspecialchars($name) ?>"
                   class="<?= isset($formErrors['name']) ? 'error-field' : '' ?>">
            <?php if (isset($formErrors['name'])): ?>
                <div class="error-message"><?= htmlspecialchars($formErrors['name']) ?></div>
                <div class="allowed-chars">Допустимые символы: буквы (русские/латинские), пробелы, дефис, апостроф (2–150 символов)</div>
            <?php endif; ?>
        </div>

        <!-- Телефон -->
        <div class="field-group">
            <label for="phone">Телефон</label>
            <input type="tel" name="phone" id="phone"
                   value="<?= htmlspecialchars($phone) ?>"
                   class="<?= isset($formErrors['phone']) ? 'error-field' : '' ?>">
            <?php if (isset($formErrors['phone'])): ?>
                <div class="error-message"><?= htmlspecialchars($formErrors['phone']) ?></div>
                <div class="allowed-chars">Допустимые символы: цифры, +, -, пробелы, скобки</div>
            <?php endif; ?>
        </div>

        <!-- Email -->
        <div class="field-group">
            <label for="email">Email *</label>
            <input type="email" name="email" id="email"
                   value="<?= htmlspecialchars($email) ?>"
                   class="<?= isset($formErrors['email']) ? 'error-field' : '' ?>">
            <?php if (isset($formErrors['email'])): ?>
                <div class="error-message"><?= htmlspecialchars($formErrors['email']) ?></div>
                <div class="allowed-chars">Email должен быть корректным адресом (например, user@example.com)</div>
            <?php endif; ?>
        </div>

        <!-- Дата рождения -->
        <div class="field-group">
            <label for="birthdate">Дата рождения</label>
            <input type="date" name="birthdate" id="birthdate"
                   value="<?= htmlspecialchars($birthdate) ?>"
                   class="<?= isset($formErrors['birthdate']) ? 'error-field' : '' ?>">
            <?php if (isset($formErrors['birthdate'])): ?>
                <div class="error-message"><?= htmlspecialchars($formErrors['birthdate']) ?></div>
                <div class="allowed-chars">Формат: ГГГГ-ММ-ДД</div>
            <?php endif; ?>
        </div>

        <!-- Пол -->
        <div class="field-group">
            <label>Пол *</label>
            <label><input type="radio" name="gender" value="male" <?= $gender === 'male' ? 'checked' : '' ?>> Мужской</label>
            <label><input type="radio" name="gender" value="female" <?= $gender === 'female' ? 'checked' : '' ?>> Женский</label>
            <?php if (isset($formErrors['gender'])): ?>
                <div class="error-message"><?= htmlspecialchars($formErrors['gender']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Биография -->
        <div class="field-group">
            <label for="bio">Биография</label>
            <textarea name="bio" id="bio" rows="5"
                      class="<?= isset($formErrors['bio']) ? 'error-field' : '' ?>"><?= htmlspecialchars($bio) ?></textarea>
            <?php if (isset($formErrors['bio'])): ?>
                <div class="error-message"><?= htmlspecialchars($formErrors['bio']) ?></div>
                <div class="allowed-chars">Допустимы буквы, цифры, пробелы, знаки препинания (.,!?;:()\-"—') и перенос строки</div>
            <?php endif; ?>
        </div>

        <!-- Языки программирования (пример справочника) -->
        <div class="field-group">
            <label>Языки программирования *</label>
            <div class="checkbox-group">
                <?php
                // Здесь нужно получить список языков из БД, но для простоты зададим статически
                $languagesList = [
                    1 => 'PHP',
                    2 => 'JavaScript',
                    3 => 'Python',
                    4 => 'Java',
                    5 => 'C++'
                ];
                foreach ($languagesList as $id => $langName):
                    $checked = in_array($id, $languages) ? 'checked' : '';
                ?>
                    <label><input type="checkbox" name="languages[]" value="<?= $id ?>" <?= $checked ?>> <?= htmlspecialchars($langName) ?></label>
                <?php endforeach; ?>
            </div>
            <?php if (isset($formErrors['languages'])): ?>
                <div class="error-message"><?= htmlspecialchars($formErrors['languages']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Согласие -->
        <div class="field-group">
            <label><input type="checkbox" name="contract" value="1" <?= $contract ? 'checked' : '' ?>> Согласие на обработку данных *</label>
            <?php if (isset($formErrors['contract'])): ?>
                <div class="error-message"><?= htmlspecialchars($formErrors['contract']) ?></div>
            <?php endif; ?>
        </div>

        <button type="submit">Отправить</button>
    </form>
</body>
</html>