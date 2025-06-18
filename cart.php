<?php
session_start();
include 'db.php';
include 'header.php';

// Initialize cart if not set
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add item to cart
if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $product = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();
    if ($product) {
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity']++;
        } else {
            $_SESSION['cart'][$id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => 1
            ];
        }
    }
    header("Location: cart.php");
    exit;
}

// Remove item from cart
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php");
    exit;
}

// Update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    foreach ($_POST['qty'] as $id => $qty) {
        $id = (int) $id;
        $qty = (int) $qty;
        if (isset($_SESSION['cart'][$id])) {
            if ($qty > 0) {
                $_SESSION['cart'][$id]['quantity'] = $qty;
            } else {
                unset($_SESSION['cart'][$id]);
            }
        }
    }
    header("Location: cart.php");
    exit;
}

// Calculate totals
$grandTotal = 0;
$itemCount = 0;
foreach ($_SESSION['cart'] as $item) {
    if (!is_array($item)) continue;
    $grandTotal += $item['price'] * $item['quantity'];
    $itemCount += $item['quantity'];
}
$tax = round($grandTotal * 0.02, 2);
$finalTotal = $grandTotal + $tax;
?>

<div class="row">
    <div class="col-md-8">
        <h3 class="mb-4"><strong>Your <span class="text-warning">Cart</span></strong></h3>

        <?php if (empty(array_filter($_SESSION['cart'], 'is_array'))): ?>
            <p>Your cart is empty.</p>
            <a href="index.php" class="btn btn-warning">Start Shopping</a>
        <?php else: ?>
            <form method="post" action="cart.php">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Product Details</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" width="60" class="me-3">
                                            <div>
                                                <div><?= htmlspecialchars($item['name']) ?></div>
                                                <a href="cart.php?action=remove&id=<?= $id ?>" class="text-danger small">Remove</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>$<?= number_format($item['price'], 2) ?></td>
                                    <td>
                                        <div class="input-group input-group-sm" style="width: 92px;">
                                            <button type="button" class="btn btn-outline-secondary qty-btn" data-id="<?= $id ?>" data-action="decrease">-</button>
                                            <input type="number" name="qty[<?= $id ?>]" value="<?= $item['quantity'] ?>" class="form-control text-center qty-input" min="1" data-id="<?= $id ?>" data-price="<?= $item['price'] ?>">
                                            <button type="button" class="btn btn-outline-secondary qty-btn" data-id="<?= $id ?>" data-action="increase">+</button>
                                        </div>
                                    </td>
                                    <td class="subtotal" id="subtotal-<?= $id ?>">$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" name="update_qty" class="btn btn-secondary mb-3">Update Quantities</button>
            </form>

            <a href="index.php" class="text-decoration-none text-warning"><strong>&larr; Continue Shopping</strong></a>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <div class="border p-4 rounded shadow-sm bg-light">
            <h5 class="mb-3">PROMO CODE</h5>
            <div class="input-group mb-4">
                <input type="text" class="form-control" placeholder="Enter promo code">
                <button class="btn btn-warning text-white">Apply</button>
            </div>

            <ul class="list-group mb-3">
                <li class="list-group-item d-flex justify-content-between">
                    <span>ITEMS <span id="item-count"><?= $itemCount ?></span></span>
                    <strong id="subtotal-amount">$<?= number_format($grandTotal, 2) ?></strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Shipping Fee</span>
                    <strong class="text-success">Free</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Tax (2%)</span>
                    <strong id="tax-amount">$<?= number_format($tax, 2) ?></strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Total</span>
                    <strong id="total-amount">$<?= number_format($finalTotal, 2) ?></strong>
                </li>
            </ul>

            <button class="btn btn-warning w-100 text-white">Place Order</button>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.qty-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const action = this.dataset.action;
            const input = document.querySelector(`.qty-input[data-id="${id}"]`);
            let qty = parseInt(input.value);

            if (action === 'increase') {
                qty++;
            } else if (action === 'decrease' && qty > 1) {
                qty--;
            }

            input.value = qty;
            updateSubtotal(id);
            updateTotal();
        });
    });

    function updateSubtotal(id) {
        const input = document.querySelector(`.qty-input[data-id="${id}"]`);
        const price = parseFloat(input.dataset.price);
        const quantity = parseInt(input.value);
        const subtotalCell = document.getElementById(`subtotal-${id}`);
        subtotalCell.textContent = `$${(price * quantity).toFixed(2)}`;
    }

    function updateTotal() {
        let grandTotal = 0;
        let itemCount = 0;
        document.querySelectorAll('.qty-input').forEach(input => {
            const price = parseFloat(input.dataset.price);
            const qty = parseInt(input.value);
            grandTotal += price * qty;
            itemCount += qty;
        });

        const tax = +(grandTotal * 0.02).toFixed(2);
        const finalTotal = grandTotal + tax;

        document.getElementById('item-count').textContent = itemCount;
        document.getElementById('subtotal-amount').textContent = `$${grandTotal.toFixed(2)}`;
        document.getElementById('tax-amount').textContent = `$${tax.toFixed(2)}`;
        document.getElementById('total-amount').textContent = `$${finalTotal.toFixed(2)}`;
    }
</script>

<?php include 'footer.php'; ?>