<?php
include('./config/config.inc.php');
include('./init.php');

$mode = isset($_GET['mode']) && $_GET['mode'] === 'visual' ? 'visual' : 'exact';

echo '<h2>Duplicate Product Images (' . strtoupper($mode) . ' match)</h2>';
echo '<p>Switch mode: 
    <a href="?mode=exact">Exact (MD5)</a> | 
    <a href="?mode=visual">Visual (pHash)</a>
</p>';

// === pHash functions ===
function perceptualHash($path)
{
    $info = getimagesize($path);
    if (!$info) return false;

    switch ($info['mime']) {
        case 'image/webp':
            $img = imagecreatefromwebp($path);
            break;
        case 'image/jpeg':
            $img = imagecreatefromjpeg($path);
            break;
        case 'image/png':
            $img = imagecreatefrompng($path);
            break;
        default:
            return false;
    }

    $small = imagescale($img, 8, 8);
    imagefilter($small, IMG_FILTER_GRAYSCALE);

    $values = [];
    for ($y = 0; $y < 8; $y++) {
        for ($x = 0; $x < 8; $x++) {
            $rgb = imagecolorat($small, $x, $y);
            $gray = ($rgb >> 16 & 0xFF) * 0.3 + ($rgb >> 8 & 0xFF) * 0.59 + ($rgb & 0xFF) * 0.11;
            $values[] = $gray;
        }
    }

    $avg = array_sum($values) / count($values);
    $hash = '';
    foreach ($values as $v) {
        $hash .= ($v >= $avg) ? '1' : '0';
    }

    imagedestroy($img);
    imagedestroy($small);

    return $hash;
}

function hammingDistance($hash1, $hash2)
{
    $len = min(strlen($hash1), strlen($hash2));
    $dist = 0;
    for ($i = 0; $i < $len; $i++) {
        if ($hash1[$i] !== $hash2[$i]) $dist++;
    }
    return $dist;
}

// === Delete single image ===
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
    if (!empty($_POST['all_ids'])) {
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

// === Find duplicates ===
$products = Db::getInstance()->executeS('SELECT id_product FROM ' . _DB_PREFIX_ . 'product');
$duplicate_ids = [];
$table_rows = '';

foreach ($products as $product) {
    $id_product = (int)$product['id_product'];
    $images = Db::getInstance()->executeS('SELECT id_image, position FROM ' . _DB_PREFIX_ . 'image WHERE id_product = ' . $id_product . ' ORDER BY position');

    $hashes = [];

    foreach ($images as $img) {
        $id_image = (int)$img['id_image'];
        $path = _PS_IMG_DIR_ . 'p/' . implode('/', str_split($id_image)) . '/' . $id_image . '.jpg';
        if (!file_exists($path)) continue;

        $hash = ($mode === 'visual') ? perceptualHash($path) : md5_file($path);
        if ($hash === false) continue;

        $duplicate_of = null;

        foreach ($hashes as $existing_hash => $existing_id) {
            if ($mode === 'exact') {
                if ($hash === $existing_hash) {
                    $duplicate_of = $existing_id;
                    break;
                }
            } else {
                $distance = hammingDistance($hash, $existing_hash);
                if ($distance < 5) {
                    $duplicate_of = $existing_id;
                    break;
                }
            }
        }

        if ($duplicate_of) {
            $frontLink = Context::getContext()->link->getProductLink($id_product);

            $table_rows .= '<tr>';
            $table_rows .= '<td>' . $id_product . '</td>';
            $table_rows .= '<td>' . $id_image . '</td>';
            $table_rows .= '<td>ID ' . $duplicate_of . ($mode === 'visual' ? " (visual)" : " (exact)") . '</td>';
            $table_rows .= '<td><a href="' . htmlspecialchars($frontLink) . '" target="_blank">?? View Product</a></td>';
            $table_rows .= '<td><a href="?delete_id=' . $id_image . '&mode=' . $mode . '" onclick="return confirm(\'Delete this duplicate?\');">Delete</a></td>';
            $table_rows .= '</tr>';

            $duplicate_ids[] = $id_image;
        } else {
            $hashes[$hash] = $id_image;
        }
    }
}

// === Render results ===
if (count($duplicate_ids) > 0) {
    echo '<form method="POST" action="?delete_all=1&mode=' . $mode . '" onsubmit="return confirm(\'Are you sure you want to delete all ' . count($duplicate_ids) . ' duplicate images?\');">';
    echo '<input type="hidden" name="all_ids" value="' . implode(',', $duplicate_ids) . '">';
    echo '<button type="submit" style="margin:10px 0; background-color:#c00; color:white; padding:6px 12px; border:none; border-radius:4px; cursor:pointer;">?? Delete All (' . count($duplicate_ids) . ')</button>';
    echo '</form>';

    echo '<table border="1" style="border-collapse: collapse; margin-top:10px;">
    <tr>
      <th>Product ID</th>
      <th>Image ID</th>
      <th>Duplicate Of</th>
      <th>Front Link</th>
      <th>Action</th>
    </tr>';
    echo $table_rows;
    echo '</table>';
} else {
    echo '<p>No duplicate images found.</p>';
}
?>
