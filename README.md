# 🌟 LinkHub — Link in Bio Service

**LinkHub** — це персональний веб-сервіс (аналог Linktree), створений для об'єднання всіх ваших важливих посилань на одній стильній сторінці. Користувачі можуть реєструватися, налаштовувати дизайн свого профілю та відстежувати статистику кліків.

![Preview](main.png)
---

## ✨ Основні можливості

* **Персоналізація профілю:** * Зміна відображуваного імені та опису (BIO).
    * Налаштування градієнтного фону сторінки.
    * Вибір кольору кнопок та тексту.
* **Динамічні посилання:**
    * Додавання необмеженої кількості посилань.
    * **Підтримка іконок:** можливість додати емодзі до кожного посилання.
* **Аналітика:** Вбудований лічильник переглядів (кліків) для кожного посилання.
* **Анімація:** Ефектні анімовані частинки (particles) на фоні та плавні переходи.
* **Безпека:** Реєстрація та авторизація з використанням сучасного хешування паролів (`password_hash`).
  
![Preview](link.png) ![Preview](login.png)
---

## 🛠 Технологічний стек

* **Backend:** PHP 8.x
* **Database:** MySQL
* **Frontend:** HTML5, CSS3 (Flexbox/Grid), JavaScript (Vanilla)
* **Server:** Сумісний з XAMPP / MAMP / Apache

---

## 🚀 Як запустити проект локально

### 1. Підготовка середовища
Вам знадобиться локальний сервер (наприклад, **XAMPP** або **MAMP**). 
Перенесіть файли проекту у папку `htdocs` (для XAMPP) або `htdocs` / `www` вашого сервера.

### 2. Налаштування бази даних
Створіть базу даних в `phpMyAdmin` і виконайте наступний SQL-запит для створення таблиць:

```sql
-- Таблиця користувачів
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблиця профілів
CREATE TABLE profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    display_name VARCHAR(100),
    bio TEXT,
    bg_color1 VARCHAR(7) DEFAULT '#667eea',
    bg_color2 VARCHAR(7) DEFAULT '#764ba2',
    button_color VARCHAR(7) DEFAULT '#ffffff',
    button_text_color VARCHAR(7) DEFAULT '#000000',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Таблиця посилань
CREATE TABLE links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    url VARCHAR(255) NOT NULL,
    icon VARCHAR(255) DEFAULT '', -- Поле для іконок/емодзі
    position INT DEFAULT 0,
    clicks INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 2. Налаштування бази даних
Створіть базу даних в `phpMyAdmin` і виконайте наступний SQL-запит для створення таблиць:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ваша_назва_бд');
```
