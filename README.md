# Duplicate Product Image Cleaner for PrestaShop

This PHP script scans all product images in a PrestaShop installation, detects duplicates based on file content (MD5 hash), and allows you to delete them individually or all at once. Tested on prestahop 8.2.

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

1. Place the script file (e.g. `duplicate_image_cleaner.php`) into your PrestaShop root directory or somewhere else
2. Make sure to backup before trying, I do not take any responsibility
3. Run URL/(directory)/duplicates.php , if it is in root just run URL/duplicates.php
4. Remove afterwards for safety
