<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $offer = $_POST['offer'];

    $imageNames = [];
    foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
        $imageName = uniqid() . '-' . $_FILES['images']['name'][$key];
        $target = '../uploads/' . $imageName;
        if (move_uploaded_file($tmpName, $target)) {
            $imageNames[] = $imageName;
        }
    }
    $imagesString = implode(',', $imageNames);

    $sql = "INSERT INTO products (name, description, category, price, offer_price, images)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdds", $name, $desc, $category, $price, $offer, $imagesString);
    $stmt->execute();
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE id = $id");
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
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        textarea {
            width: 90%;
            height: 80px;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        table,
        th,
        td {
            border: 1px solid #ccc;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            vertical-align: middle;
        }

        img {
            max-width: 40px;
            height: auto;
            margin-right: 4px;
            vertical-align: middle;
        }

        .actions a {
            padding: 5px 10px;
            margin-right: 5px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
        }

        .edit-btn {
            background-color: #007bff;
        }

        .delete-btn {
            background-color: #dc3545;
        }

        footer {
            text-align: center;
            padding: 20px;
            background: #f4f4f4;
            border-top: 1px solid #ccc;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <h2>Product List</h2>
    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Offer Price</th>
                <th>Stock</th>
                <th>Rating</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM products ORDER BY id DESC");
            while ($row = $result->fetch_assoc()):
                $images = explode(',', $row['images']);
                $firstImage = $images[0] ?? '';
            ?>
                <tr>
                    <td>
                        <?php if ($firstImage): ?>
                            <img src="../uploads/<?= htmlspecialchars($firstImage) ?>" alt="Product Image">
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td>$<?= number_format($row['price'], 2) ?></td>
                    <td>$<?= number_format($row['offer_price'], 2) ?></td>
                    <td><?= $row['stock'] ?></td>
                    <td><?= isset($row['rating']) ? htmlspecialchars($row['rating']) : 'N/A' ?></td>
                    <td class="actions">
                        <a href="edit_product.php?id=<?= $row['id'] ?>" class="edit-btn">Edit</a>
                        <a href="?delete=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Delete this product?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>



    <footer>
        &copy; <?= date('Y') ?> QuickCart Seller Panel - Indonesia
    </footer>
    </div>
    </div>
</body>

</html>