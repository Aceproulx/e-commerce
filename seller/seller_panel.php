<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $offer = $_POST['offer'];

    $imageNames = [];

    // Handle file uploads
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

    // Handle image links
    if (!empty($_POST['image_links'])) {
        $links = explode(',', $_POST['image_links']);
        foreach ($links as $link) {
            $link = trim($link);
            if (filter_var($link, FILTER_VALIDATE_URL)) {
                $imageNames[] = $link;
            }
        }
    }

    $imagesString = implode(',', $imageNames);

    $sql = "INSERT INTO products (name, description, category, price, offer_price, image)
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
    <div class="container">
        <div class="sidebar">
            <h2>QuickCart</h2>
            <a href="#">Add Product</a>
            <a href="productlist.php">Product List</a>
        </div>
        <div class="content">
            <h2>Add Product</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <label>Product Images <br></label>
                <input type="file" name="images[]" multiple style="width: 400px;"><br>

                <label>Or provide Image URL(s) (comma-separated) <br></label>
                <input type="text" name="image_links" placeholder="https://example.com/img1.jpg, https://example.com/img2.jpg" style="width: 400px;"><br>

                <label>Product Name <br></label>
                <input type="text" name="name" required style="width: 400px;"><br>

                <label>Product Description <br></label>
                <textarea name="description" style="width: 400px;" placeholder="Enter product details..."></textarea><br>

                <label>Category <br></label>
                <select name="category" style="width: 420px;">
                    <option value="Earphone">Earphone</option>
                    <option value="Smartphone">Smartphone</option>
                    <option value="Laptop">Laptop</option>
                </select>

                <label><br>Product Price <br></label>
                <input type="number" name="price" required min="0" style="width: 400px;"><br>

                <label>Offer Price <br></label>
                <input type="number" name="offer" required min="0" style="width: 400px;"><br>

                <button type="submit">ADD</button>
            </form>

            <footer>
                &copy; <?= date('Y') ?> QuickCart Seller Panel - Indonesia
            </footer>
        </div>
    </div>
</body>

</html>