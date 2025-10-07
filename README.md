# 🧹 Duplicate Product Image Cleaner for PrestaShop

This PHP utility scans all product images in a PrestaShop installation, detects duplicates based on **exact MD5 checksum** or **visual similarity (pHash)**, and allows you to delete them individually or all at once.
Tested on **PrestaShop 8.2**.

---

## 🚀 Features

* 🔍 Detects duplicate product images using:

  * **MD5 checksum** for exact byte-level matches
  * **pHash (perceptual hash)** for visually identical images (different compression, size, etc.)
* 🧾 Displays results in a clear, sortable HTML table
* 🌐 Shows direct **Front Office links** for quick visual verification
* 🗑 Allows **individual deletion** of duplicates
* 🧨 Includes a **“Delete All (X)”** button to remove all detected duplicates at once
* 💾 Deletes both the **image file** and its **database record** from `ps_image`
* ⚙️ Works directly with your existing PrestaShop setup — no modules, no overrides

---

## ⚙️ Requirements

* PHP **7.4+** (recommended: 8.x)
* A working **PrestaShop installation**
* Access to PrestaShop’s core files (`config/config.inc.php`, `init.php`)
* Optional: GD or ImageMagick enabled (required for **visual mode**)

---

## 📂 Installation

1. Download the script and save it as

   ```bash
   duplicate_image_cleaner.php
   ```

   inside your PrestaShop root directory (where `config/` and `img/` folders are).

2. **Make a full backup** of your `/img/` folder and database before using it.
   *(I take no responsibility for accidental data loss.)*

3. Open it in your browser:

   ```
   https://yourshop.com/duplicate_image_cleaner.php
   ```

4. Choose mode:

   * `?mode=exact` → Compare using MD5 checksums
   * `?mode=visual` → Compare using perceptual hashing (detects visually identical images)

5. Review duplicates in the generated table.
   You can:

   * Delete specific images via the **“Delete”** button, or
   * Remove all at once with **“Delete All (X)”**

6. Delete the script from your server once finished for safety.

---

## 🧠 Notes

* **Visual mode** may take longer to process since it computes image hashes.
* The script currently checks only **product images** (not categories or manufacturers).
* It does **not** regenerate thumbnails — you can do that later from the Back Office.
* Best used on staging or test environments before running in production.

---

## 📸 Example URLs

```
https://yourshop.com/duplicate_image_cleaner.php?mode=exact
https://yourshop.com/duplicate_image_cleaner.php?mode=visual
```

---

Would you like me to make a Slovak version of this README as well (napr. `README_SK.md`)?
