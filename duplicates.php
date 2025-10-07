<?php
include('./config/config.inc.php');
include('./init.php');

$shop_root = $_SERVER['DOCUMENT_ROOT'] . '/';

// === Delete a single image ===
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];

    $path = _PS_IMG_DIR_ . 'p/' . implode('/', str_split($delete_id)) . '/' . $delete_id . '.jpg';

    if (file_exists($path)) {
        if (unlink($path)) {
            echo '<p style="color:green;">Image ID ' . $delete_id . ' has been deleted.</p>';
            Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'image WHERE id_image = ' . $delete_id);
        } else {
            echo '<p style="color:red;">Failed to delete image ID ' . $delete_id . '</p>';
        }
    } else {
        echo '<p style="color:red;">Image ID ' . $delete_id . ' does not exist.</p>';
    }
}

// === Delete all duplicates ===
if (isset($_GET['delete_all']) && $_GET['delete_all'] == 1) {
    if (isset($_POST['all_ids']) && $_POST['all_ids'] !== '') {
        $ids_to_delete = explode(',', $_POST['all_ids']);
        $deleted_count = 0;

        foreach ($ids_to_delete as $delete_id) {
            $delete_id = (int)$delete_id;
            $path = _PS_IMG_DIR_ . 'p/' . implode('/', str_split($delete_id)) . '/' . $delete_id . '.jpg';

            if (file_exists($path)) {
                if (unlink($path)) {
                    Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'image WHERE id_image = ' . $delete_id);
                    $deleted_count++;
                }
            }
        }

        echo '<p style="color:green;">Successfully deleted ' . $deleted_count . ' duplicate images.</p>';
    }
}

echo '<h2>Duplicate Product Images</h2>';

$products = Db::getInstance()->executeS('SELECT id_product FROM ' . _DB_PREFIX_ . 'product');
$duplicate_ids = []; // store IDs of duplicate images

ob_start(); // capture table output so we can display it after the delete-all button
echo '<table border="1" style="border-collapse: collapse;"><tr><th>Product ID</th><th>Image ID</th><th>Duplicate Of</th><th>Action</th></tr>';

foreach ($products as $product) {
    $id_product = (int)$product['id_product'];
    $images = Db::getInstance()->executeS('SELECT id_image, position FROM ' . _DB_PREFIX_ . 'image WHERE id_product = ' . $id_product . ' ORDER BY position');

    $hashes = [];

    foreach ($images as $img) {
        $id_image = (int)$img['id_image'];
        $path = _PS_IMG_DIR_ . 'p/' . implode('/', str_split($id_image)) . '/' . $id_image . '.jpg';

        if (file_exists($path)) {
            $hash = md5_file($path);

            if (isset($hashes[$hash])) {
                // Duplicate image found
                echo '<tr>';
                echo '<td>' . $id_product . '</td>';
                echo '<td>' . $id_image . '</td>';
                echo '<td>ID ' . $hashes[$hash] . '</td>';
                echo '<td><a href="?delete_id=' . $id_image . '" onclick="return confirm(\'Are you sure you want to delete this duplicate image?\');">Delete</a></td>';
                echo '</tr>';

                $duplicate_ids[] = $id_image;
            } else {
                $hashes[$hash] = $id_image;
            }
        }
    }
}
echo '</table>';
$table_html = ob_get_clean(); // save the table output

// === "Delete All" button ===
$dup_count = count($duplicate_ids);

if ($dup_count > 0) {
    echo '<form method="POST" action="?delete_all=1" onsubmit="return confirm(\'Are you sure you want to delete all ' . $dup_count . ' duplicate images?\');">';
    echo '<input type="hidden" name="all_ids" value="' . implode(',', $duplicate_ids) . '">';
    echo '<button type="submit" style="margin-bottom:10px; background-color:#c00; color:white; padding:6px 12px; border:none; border-radius:4px; cursor:pointer;">Delete All (' . $dup_count . ')</button>';
    echo '</form>';
} else {
    echo '<p>No duplicate images found.</p>';
}

// Output the table
echo $table_html;
?>
