<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$_SESSION["csrf"] ??= bin2hex(random_bytes(32));

$stmt = $pdo->query("SELECT id, name, category, price, stock FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();

$status = $_GET["status"] ?? "";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Manager</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <header class="header">
        <div>
            <h1>Product Manager</h1>
            <p>Manajemen data produk PHP + MySQL</p>
        </div>
        <a class="btn primary" href="create.php">+ Tambah Produk</a>
    </header>

    <?php if ($status === "created"): ?>
        <div class="alert success">Produk berhasil ditambahkan.</div>
    <?php elseif ($status === "updated"): ?>
        <div class="alert success">Produk berhasil diperbarui.</div>
    <?php elseif ($status === "deleted"): ?>
        <div class="alert success">Produk berhasil dihapus.</div>
    <?php endif; ?>

    <section class="products">
        <?php if (!$products): ?>
            <div class="empty">Belum ada produk.</div>
        <?php endif; ?>

        <?php foreach ($products as $product): ?>
            <article class="card">
                <h2><?= htmlspecialchars($product["name"], ENT_QUOTES, "UTF-8") ?></h2>
                <p class="category"><?= htmlspecialchars($product["category"], ENT_QUOTES, "UTF-8") ?></p>
                <p class="price">Rp <?= number_format($product["price"], 0, ",", ".") ?></p>
                <p>Stok: <?= (int)$product["stock"] ?></p>

                <div class="actions">
                    <a class="btn secondary" href="edit.php?id=<?= (int)$product["id"] ?>">Edit</a>

                    <form method="POST" action="delete.php" onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
                        <input type="hidden" name="id" value="<?= (int)$product["id"] ?>">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION["csrf"], ENT_QUOTES, "UTF-8") ?>">
                        <button class="btn danger" type="submit">Hapus</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
</div>
</body>
</html>
