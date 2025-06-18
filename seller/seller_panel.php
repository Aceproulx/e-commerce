<?php
include '../db.php';

$editMode = false;
$editProduct = [];

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE id = $id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch product for editing
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM products WHERE id = $editId");
    if ($res->num_rows > 0) {
        $editProduct = $res->fetch_assoc();
        $editMode = true;
    }
}

// Handle form submission (add or update)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = (float) $_POST['price'];
    $offer = (float) $_POST['offer'];
    $category_input = trim($_POST['category']);

    // Handle category
    if (is_numeric($category_input)) {
        $category_id = (int)$category_input;
    } else {
        $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->bind_param("s", $category_input);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $category_id = $row['id'];
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $category_input);
            $stmt->execute();
            $category_id = $stmt->insert_id;
        }
    }

    // Handle images
    $imageNames = [];
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            if (!empty($tmpName)) {
                $imageName = uniqid() . '-' . $_FILES['images']['name'][$key];
                $target = '../uploads/' . $imageName;
                if (move_uploaded_file($tmpName, $target)) {
                    $imageNames[] = $imageName;
                }
            }
        }
    }

    if (!empty($_POST['image_link'])) {
        $link = trim($link);
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            $imageNames[] = $link;
        }
    }

    $imagesString = implode(',', $imageNames);

    // Insert or update
    if (isset($_POST['edit_id'])) {
        $editId = intval($_POST['edit_id']);
        $sql = "UPDATE products SET name=?, description=?, category_id=?, price=?, offer_price=?";
        if (!empty($imagesString)) {
            $sql .= ", image=?";
        }
        $sql .= " WHERE id=?";
        if (!empty($imagesString)) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiddsi", $name, $desc, $category_id, $price, $offer, $imagesString, $editId);
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiddi", $name, $desc, $category_id, $price, $offer, $editId);
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO products (name, description, category_id, price, offer_price, image) 
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssidds", $name, $desc, $category_id, $price, $offer, $imagesString);
    }

    $stmt->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Seller Panel</title>
    <style>
        body {
            margin: 0;
            font-family: Arial;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 220px;
            background: #f4f4f4;
            padding: 20px;
            border-right: 1px solid #ccc;
        }

        .sidebar h2 {
            font-size: 22px;
            color: #ff6600;
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
            margin-bottom: 5px;
        }

        .sidebar a:hover {
            background: #ddd;
        }

        .content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        input,
        select,
        textarea {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        textarea {
            resize: vertical;
        }

        button {
            padding: 10px 20px;
            background: #ff6600;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }

        footer {
            text-align: center;
            padding: 20px;
            background: #f4f4f4;
            border-top: 1px solid #ccc;
            margin-top: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <h2>QuickCart</h2>
            <div>Seller's Panel</div>

        </div>

        <div class="content">
            <h2><?= $editMode ? 'Edit' : 'Add' ?> Product</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <?php if ($editMode): ?>
                    <input type="hidden" name="edit_id" value="<?= $editProduct['id'] ?>">
                <?php endif; ?>

                <label>Product Images <br></label>
                <input type="file" name="images[]" multiple style="width: 400px;"><br>

                <label>Or provide Image URL<br></label>
                <input type="text" name="image_link" placeholder="https://burst.shopifycdn.com/photos/wireless-headphones.jpg" style="width: 400px;"><br>

                <label>Product Name <br></label>
                <input type="text" name="name" required style="width: 400px;" value="<?= $editProduct['name'] ?? '' ?>"><br>

                <label>Product Description <br></label>
                <textarea name="description" style="width: 400px; height: 80px;"><?= $editProduct['description'] ?? '' ?></textarea><br>

                <label>Category <br></label>
                <select name="category" style="width: 420px;">
                    <option value="">Select Category</option>
                    <?php
                    $catResult = $conn->query("SELECT id, name FROM categories");
                    while ($cat = $catResult->fetch_assoc()):
                        $selected = isset($editProduct['category_id']) && $editProduct['category_id'] == $cat['id'] ? 'selected' : '';
                        echo "<option value='{$cat['id']}' $selected>{$cat['name']}</option>";
                    endwhile;
                    ?>
                </select><br>

                <label>Product Price <br></label>
                <input type="number" name="price" required min="0" step="0.01" style="width: 400px;" value="<?= $editProduct['price'] ?? '' ?>"><br>


                <label>Offer Price <br></label>
                <input type="number" name="offer" required min="0" style="width: 400px;" value="<?= $editProduct['offer_price'] ?? '' ?>"><br>

                <button type="submit"><?= $editMode ? 'UPDATE' : 'ADD' ?></button>
            </form>

            <h3>Product List</h3>
            <table>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Offer</th>
                    <th>Actions</th>
                </tr>
                <?php
                $res = $conn->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
                while ($row = $res->fetch_assoc()):
                    $firstImage = explode(',', $row['image'])[0];
                    $imgSrc = (filter_var($firstImage, FILTER_VALIDATE_URL)) ? $firstImage : "../uploads/$firstImage";
                ?>
                    <tr>
                        <td><img src="<?= $imgSrc ?>" width="60"></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['category_name']) ?></td>
                        <td>$<?= number_format($row['price'], 2) ?></td>
                        <td>$<?= number_format($row['offer_price'], 2) ?></td>
                        <td>
                            <a href="?edit=<?= $row['id'] ?>">Edit</a> |
                            <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this product?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>

            <footer>
                &copy; <?= date('Y') ?> QuickCart Seller Panel - Indonesia
            </footer>
        </div>
    </div>
</body>

</html>