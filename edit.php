<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$_SESSION["csrf"] ??= bin2hex(random_bytes(32));

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id) {
    exit("ID produk tidak valid.");
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(["id" => $id]);
$product = $stmt->fetch();

if (!$product) {
    exit("Produk tidak ditemukan.");
}

$errors = [];
$name = $product["name"];
$category = $product["category"];
$price = $product["price"];
$stock = $product["stock"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "Umum");
    $price = filter_input(INPUT_POST, "price", FILTER_VALIDATE_FLOAT);
    $stock = filter_input(INPUT_POST, "stock", FILTER_VALIDATE_INT);

    if (mb_strlen($name) < 3) {
        $errors["name"] = "Nama minimal 3 karakter.";
    }
    if ($price === false || $price === null || $price <= 0) {
        $errors["price"] = "Harga harus lebih dari 0.";
    }
    if ($stock === false || $stock === null || $stock < 0) {
        $errors["stock"] = "Stok tidak boleh negatif.";
    }

    if (!$errors) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM products WHERE name = :name AND id <> :id");
        $check->execute(["name" => $name, "id" => $id]);

        if ($check->fetchColumn() > 0) {
            $errors["name"] = "Nama produk sudah digunakan.";
        }
    }

    if (!$errors) {
        $update = $pdo->prepare(
            "UPDATE products
             SET name = :name, category = :category, price = :price, stock = :stock
             WHERE id = :id"
        );
        $update->execute([
            "name" => $name,
            "category" => $category ?: "Umum",
            "price" => $price,
            "stock" => $stock,
            "id" => $id
        ]);

        header("Location: index.php?status=updated");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container small">
    <a class="back" href="index.php">← Kembali</a>
    <div class="form-card">
        <h1>Edit Produk</h1>

        <form method="POST">
            <label for="name">Nama produk</label>
            <input id="name" name="name" value="<?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?>" minlength="3" required>
            <?php if (isset($errors["name"])): ?><small class="error"><?= htmlspecialchars($errors["name"]) ?></small><?php endif; ?>

            <label for="category">Kategori</label>
            <input id="category" name="category" value="<?= htmlspecialchars($category, ENT_QUOTES, "UTF-8") ?>">

            <label for="price">Harga</label>
            <input id="price" name="price" type="number" min="1" step="0.01" value="<?= htmlspecialchars((string)$price, ENT_QUOTES, "UTF-8") ?>" required>
            <?php if (isset($errors["price"])): ?><small class="error"><?= htmlspecialchars($errors["price"]) ?></small><?php endif; ?>

            <label for="stock">Stok</label>
            <input id="stock" name="stock" type="number" min="0" value="<?= htmlspecialchars((string)$stock, ENT_QUOTES, "UTF-8") ?>" required>
            <?php if (isset($errors["stock"])): ?><small class="error"><?= htmlspecialchars($errors["stock"]) ?></small><?php endif; ?>

            <button class="btn primary full" type="submit">Simpan Perubahan</button>
        </form>
    </div>
</div>
</body>
</html>
