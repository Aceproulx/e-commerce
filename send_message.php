<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Optionally validate/sanitize
    header("Location: contact.php?msg=success");
    exit;
}
