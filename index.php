<?php
include 'db.php';
session_start();
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>All Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        #heroCarousel {
            margin-bottom: 10px;
        }

        .product-card {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 15px;
            background: #fff;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .carousel-item {
            min-height: 400px;
            height: 300px;
        }

        .carousel-item img {
            height: 100%;
            width: auto;
            object-fit: cover;
        }

        .carousel-caption-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f8f9fa;
            height: 100%;
            padding: 2rem;
            border-radius: 10px;
        }

        .product-img {
            max-height: 180px;
            object-fit: contain;
        }

        .truncate {
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .wishlist-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.4rem;
            color: #bbb;
        }

        .wishlist-btn:hover {
            color: red;
        }
    </style>
</head>

<body class="bg-light">
    <!-- Hero Section -->
    <div class="hero mb-5">
        <div>
            <h1 class="display-5 fw-bold">Explore All Products</h1>
            <p class="lead">Find the best items just for you.</p>
        </div>
    </div>
    <!-- Carousel Start -->
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2000">
        <div class="carousel-inner">
            <!-- Slide 1 -->
            <div class="carousel-item active">
                <div class="carousel-caption-container">
                    <div>
                        <p class="text-danger fw-semibold">Limited Time Offer 30% Off</p>
                        <h2 class="fw-bold mb-3">Experience Pure Sound -<br>Your Perfect Headphones Awaits!</h2>
                        <a href="#" class="btn btn-warning me-2">Buy Now</a>
                        <a href="#" class="btn btn-link">Find more →</a>
                    </div>
                    <img src="https://burst.shopifycdn.com/photos/wireless-headphones.jpg?width=373&format=pjpg&exif=0&iptc=0"
                        class="img-fluid" alt="Headphones">
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="carousel-item">
                <div class="carousel-caption-container">
                    <div>
                        <p class="text-danger fw-semibold">Flash Sale</p>
                        <h2 class="fw-bold mb-3">New Smartphones Out Now!</h2>
                        <a href="#" class="btn btn-primary me-2">Buy Now</a>
                        <a href="#" class="btn btn-link">Explore →</a>
                    </div>
                    <img src="https://fdn2.gsmarena.com/vv/pics/samsung/samsung-galaxy-s23-5g-1.jpg"
                        class="img-fluid" alt="Smartphone">
                </div>
            </div>
        </div>

        <!-- Controls -->
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>

        <!-- Indicators -->
        <div class="carousel-indicators mt-3">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
        </div>
    </div>

    <!-- Products Section -->
    <div class="container py-5">
        <h3 class="mb-4">All products</h3>

        <!-- Filter -->
        <form method="get" class="mb-4 d-flex flex-wrap align-items-center gap-2">
            <select name="category" class="form-select w-auto">
                <option value="">All Categories</option>
                <?php
                $catResult = $conn->query("SELECT * FROM categories");
                while ($cat = $catResult->fetch_assoc()) {
                    $selected = (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : '';
                    echo "<option value='{$cat['id']}' $selected>{$cat['name']}</option>";
                }
                ?>
            </select>

            <input type="text" name="search" class="form-control w-auto" placeholder="Search products..."
                value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">

            <button type="submit" class="btn btn-secondary">Filter</button>
        </form>

        <!-- Products Grid -->
        <div class="row g-4">
            <?php
            $limit = 8;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $limit;

            // Build WHERE clause
            $whereClauses = [];

            if (isset($_GET['category']) && $_GET['category'] !== '') {
                $cat_id = (int)$_GET['category'];
                $whereClauses[] = "category_id = $cat_id";
            }

            if (isset($_GET['search']) && trim($_GET['search']) !== '') {
                $search = $conn->real_escape_string($_GET['search']);
                $whereClauses[] = "(name LIKE '%$search%' OR description LIKE '%$search%')";
            }

            $where = count($whereClauses) > 0 ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

            $totalQuery = $conn->query("SELECT COUNT(*) as total FROM products $where");
            $totalRows = $totalQuery->fetch_assoc()['total'];
            $totalPages = ceil($totalRows / $limit);

            $result = $conn->query("SELECT * FROM products $where LIMIT $offset, $limit");
            while ($row = $result->fetch_assoc()):
            ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="product-card position-relative">
                        <button class="wishlist-btn">&#9825;</button>
                        <img src="<?= htmlspecialchars($row['image']) ?>" class="img-fluid product-img w-100 mb-3" alt="<?= htmlspecialchars($row['name']) ?>">
                        <h6 class="fw-semibold truncate"><?= htmlspecialchars($row['name']) ?></h6>
                        <small class="text-muted d-block mb-1 truncate"><?= htmlspecialchars($row['description']) ?></small>
                        <?php
                        $rating = round($row['rating'], 1);
                        $fullStars = floor($rating);
                        $halfStar = ($rating - $fullStars >= 0.5);
                        ?>
                        <div class="text-danger mb-2">
                            <?php for ($i = 0; $i < $fullStars; $i++) echo '★'; ?>
                            <?php if ($halfStar) echo '½'; ?>
                            <?php for ($i = $fullStars + $halfStar; $i < 5; $i++) echo '☆'; ?>
                            <small class="text-muted"><?= $rating ?></small>
                        </div>

                        <?php if ($row['offer_price'] > 0 && $row['offer_price'] < $row['price']): ?>
                            <div class="fw-bold mb-2">
                                <span class="text-muted text-decoration-line-through">$<?= number_format($row['price'], 2) ?></span>
                                <span class="text-danger ms-2">$<?= number_format($row['offer_price'], 2) ?></span>
                            </div>
                        <?php else: ?>
                            <div class="fw-bold mb-2">$<?= number_format($row['price'], 2) ?></div>
                        <?php endif; ?>
                        <a href="cart.php?action=add&id=<?= $row['id'] ?>" class="btn btn-outline-primary w-100">Add To Cart</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <nav class="mt-5">
            <ul class="pagination justify-content-center">
                <?php
                for ($i = 1; $i <= $totalPages; $i++):
                    $link = "?page=$i";
                    if (isset($_GET['category'])) $link .= "&category=" . $_GET['category'];
                    if (isset($_GET['search'])) $link .= "&search=" . urlencode($_GET['search']);
                ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $link ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>