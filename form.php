<?php
$errors = [];
$old = [];
$values = [];
$success_id = null;

// Ошибки
if (!empty($_COOKIE['errors'])) {
    $errors = json_decode($_COOKIE['errors'], true);
    setcookie('errors', '', time() - 3600, '/');
}

// Старые данные
if (!empty($_COOKIE['old'])) {
    $old = json_decode($_COOKIE['old'], true);
    setcookie('old', '', time() - 3600, '/');
}

// Сохранённые значения
if (!empty($_COOKIE['values'])) {
    $values = json_decode($_COOKIE['values'], true);
}

// ID успешной заявки
if (!empty($_COOKIE['success_id'])) {
    $success_id = $_COOKIE['success_id'];
    setcookie('success_id', '', time() - 3600, '/');
}

function val($name) {
    global $old, $values;
    return htmlspecialchars($old[$name] ?? $values[$name] ?? '');
}

function checked($name, $value) {
    global $old, $values;
    return (($old[$name] ?? $values[$name] ?? '') == $value) ? 'checked' : '';
}

function selected($name, $value) {
    global $old, $values;
    $arr = $old[$name] ?? $values[$name] ?? [];
    return in_array($value, (array)$arr) ? 'selected' : '';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Форма</title>

<style>
* {
  box-sizing: border-box;
}

body {
  margin: 0;
  font-family: 'Segoe UI', Arial, sans-serif;
  background: linear-gradient(135deg, #74ebd5, #ACB6E5);
}

.container {
  max-width: 500px;
  margin: 50px auto;
  background: #ffffff;
  padding: 30px;
  border-radius: 15px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

h2 {
  text-align: center;
  margin-bottom: 20px;
}

label {
  display: block;
  margin-top: 15px;
  font-weight: 600;
}

input, select, textarea {
  width: 100%;
  margin-top: 5px;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #ccc;
  transition: 0.3s;
  font-size: 14px;
}

input:focus, select:focus, textarea:focus {
  border-color: #4CAF50;
  outline: none;
  box-shadow: 0 0 5px rgba(76,175,80,0.5);
}

.radio-group, .checkbox-group {
  margin-top: 10px;
}

.radio-group label,
.checkbox-group label {
  display: inline-block;
  margin-right: 15px;
  font-weight: normal;
}

select[multiple] {
  height: 120px;
}

button {
  width: 100%;
  margin-top: 20px;
  padding: 12px;
  border: none;
  border-radius: 8px;
  background: linear-gradient(135deg, #4CAF50, #2e7d32);
  color: white;
  font-size: 16px;
  cursor: pointer;
  transition: 0.3s;
}

button:hover {
  background: linear-gradient(135deg, #45a049, #1b5e20);
  transform: scale(1.02);
}

/* Ошибки */
.error {
  border: 2px solid red !important;
}

.error-text {
  color: red;
  font-size: 13px;
  margin-top: 3px;
}

/* Успех */
.success {
  background: #d4edda;
  color: #155724;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 15px;
  border: 1px solid #c3e6cb;
  font-weight: bold;
  text-align: center;
  animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
  from {opacity: 0; transform: translateY(-10px);}
  to {opacity: 1; transform: translateY(0);}
}
</style>
</head>

<body>

<div class="container">

<h2>Форма регистрации</h2>

<?php if ($success_id): ?>
  <div class="success">
    ✅ Данные успешно сохранены! ID заявки: <?= htmlspecialchars($success_id) ?>
  </div>
<?php endif; ?>

<form action="index.php" method="POST">

<label>ФИО:</label>
<input type="text" name="name" value="<?= val('name') ?>" class="<?= isset($errors['name']) ? 'error' : '' ?>">
<?php if (isset($errors['name'])): ?><div class="error-text"><?= $errors['name'] ?></div><?php endif; ?>

<label>Телефон:</label>
<input type="tel" name="phone" value="<?= val('phone') ?>" class="<?= isset($errors['phone']) ? 'error' : '' ?>">
<?php if (isset($errors['phone'])): ?><div class="error-text"><?= $errors['phone'] ?></div><?php endif; ?>

<label>Email:</label>
<input type="text" name="email" value="<?= val('email') ?>" class="<?= isset($errors['email']) ? 'error' : '' ?>">
<?php if (isset($errors['email'])): ?><div class="error-text"><?= $errors['email'] ?></div><?php endif; ?>

<label>Дата рождения:</label>
<input type="date" name="birthdate" value="<?= val('birthdate') ?>" class="<?= isset($errors['birthdate']) ? 'error' : '' ?>">
<?php if (isset($errors['birthdate'])): ?><div class="error-text"><?= $errors['birthdate'] ?></div><?php endif; ?>

<label>Пол:</label>
<div class="radio-group">
  <label><input type="radio" name="gender" value="male" <?= checked('gender','male') ?>> Муж</label>
  <label><input type="radio" name="gender" value="female" <?= checked('gender','female') ?>> Жен</label>
</div>
<?php if (isset($errors['gender'])): ?><div class="error-text"><?= $errors['gender'] ?></div><?php endif; ?>

<label>Любимый язык программирования:</label>
<select name="languages[]" multiple class="<?= isset($errors['languages']) ? 'error' : '' ?>">
<option value="1" <?= selected('languages','1') ?>>Pascal</option>
<option value="2" <?= selected('languages','2') ?>>C</option>
<option value="3" <?= selected('languages','3') ?>>C++</option>
<option value="4" <?= selected('languages','4') ?>>JavaScript</option>
<option value="5" <?= selected('languages','5') ?>>PHP</option>
<option value="6" <?= selected('languages','6') ?>>Python</option>
<option value="7" <?= selected('languages','7') ?>>Java</option>
<option value="8" <?= selected('languages','8') ?>>Haskel</option>
<option value="9" <?= selected('languages','9') ?>>Clojure</option>
<option value="10" <?= selected('languages','10') ?>>Prolog</option>
<option value="11" <?= selected('languages','11') ?>>Scala</option>
<option value="12" <?= selected('languages','12') ?>>Go</option>
</select>
<?php if (isset($errors['languages'])): ?><div class="error-text"><?= $errors['languages'] ?></div><?php endif; ?>

<label>Биография:</label>
<textarea name="bio"><?= val('bio') ?></textarea>

<div class="checkbox-group">
  <label>
    <input type="checkbox" name="contract" <?= checked('contract','1') ?>> С контрактом ознакомлен
  </label>
</div>
<?php if (isset($errors['contract'])): ?><div class="error-text"><?= $errors['contract'] ?></div><?php endif; ?>

<button type="submit">Сохранить</button>

</form>
</div>

</body>
</html>