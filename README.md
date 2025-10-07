# Duplicate Product Image Cleaner for PrestaShop

This PHP script scans all product images in a PrestaShop installation, detects duplicates based on file content (MD5 hash), and allows you to delete them individually or all at once.

---

## 🚀 Features

- Detects duplicate product images using **MD5 checksum comparison**.
- Displays duplicates in a clear HTML table.
- Allows **individual deletion** of specific duplicates.
- Includes a **"Delete All (X)"** button that removes only the detected duplicate images.
- Safely deletes both the **image file** and its **database record** from the `ps_image` table.
- Works directly with your existing PrestaShop installation.

---

## ⚙️ Requirements

- PHP (7.4+ recommended)
- A working **PrestaShop installation**
- Access to PrestaShop’s configuration files (`config.inc.php`, `init.php`)

---

## 📂 Installation

1. Place the script file (e.g. `duplicate_image_cleaner.php`) into your PrestaShop root directory or the `/admin` folder.
2. Make sure it can include:
   ```php
   include('./config/config.inc.php');
   include('./init.php');
