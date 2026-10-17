<?php
require_once "db.php";

function e($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

function showTime($time) {
    if (empty($time)) {
        return "Not listed";
    }

    return date("g:i A", strtotime($time));
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(404);
    exit("Invalid place ID. <a href='index.php'>Return home</a>");
}

$stmt = $conn->prepare("SELECT * FROM places WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$place = $result->fetch_assoc();

if (!$place) {
    http_response_code(404);
    exit("Place not found. <a href='index.php'>Return home</a>");
}

$productStmt = $conn->prepare(
    "SELECT product_name, price FROM products WHERE place_id = ? ORDER BY product_name"
);
$productStmt->bind_param("i", $id);
$productStmt->execute();
$products = $productStmt->get_result();

$wifi = $place["wifi_available"] ?? "unknown";

$wifiText = match ($wifi) {
    "yes" => "Wi-Fi reported available",
    "no" => "Wi-Fi reported unavailable",
    default => "Wi-Fi availability unconfirmed"
};

$categoryNames = [
    "food" => "Café & Food",
    "study" => "Study Spot",
    "relax" => "Chill Spot"
];

$categoryName = $categoryNames[$place["category"]] ?? "Tambayan";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($place["name"]) ?> | Tam-Bayan ng QCU</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap"
          rel="stylesheet">

    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="navbar">
    <a href="index.php" class="brand">
        <span class="brand-icon">✦</span>
        Tam-Bayan <span class="brand-qcu">ng QCU</span>
    </a>

    <nav class="nav-links">
        <a href="index.php#home">Home</a>
        <a href="index.php#tambayan">Explore</a>
        <a href="index.php#about">About</a>
    </nav>
</header>

<main class="details-main">
    <a href="index.php#tambayan" class="back-link">← Back to places</a>

    <article class="details-card">
        <?php if (!empty($place["image"])): ?>
            <img
                src="images/<?= e(basename($place["image"])) ?>"
                alt="<?= e($place["name"]) ?>"
                class="details-image"
            >
        <?php else: ?>
            <div class="details-placeholder">✧ Your next tambayan</div>
        <?php endif; ?>

        <div class="details-content">
            <span class="category-badge static-badge">
                <?= e($categoryName) ?>
            </span>

            <h1><?= e($place["name"]) ?></h1>

            <p class="details-address">
                ⌖ <?= e($place["address"]) ?>
            </p>

            <p class="details-description">
                <?= nl2br(e($place["description"] ?: "No description available yet.")) ?>
            </p>

            <div class="details-info-grid">
                <div class="detail-info-box">
                    <span class="info-label">OPENING HOURS</span>
                    <strong>
                        <?= e(showTime($place["opening_time"])) ?>
                        – <?= e(showTime($place["closing_time"])) ?>
                    </strong>
                </div>

                <div class="detail-info-box">
                    <span class="info-label">WI-FI STATUS</span>
                    <strong><?= e($wifiText) ?></strong>
                </div>
            </div>

            <?php if (!empty($place["map_url"])): ?>
                <a href="<?= e($place["map_url"]) ?>"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="btn btn-primary">
                    Open in Google Maps ↗
                </a>
            <?php endif; ?>

            <section class="products-section">
                <h2>Menu & Prices <span>✧</span></h2>

                <?php if ($products->num_rows > 0): ?>
                    <div class="product-list">
                        <?php while ($product = $products->fetch_assoc()): ?>
                            <div class="product-row">
                                <span><?= e($product["product_name"]) ?></span>
                                <strong>
                                    ₱<?= number_format((float)$product["price"], 2) ?>
                                </strong>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>Menu and prices have not been added yet.</p>
                <?php endif; ?>
            </section>

            <p class="disclaimer">
                Please confirm opening hours, menu prices, and Wi-Fi availability
                directly with the venue before visiting.
            </p>
        </div>
    </article>
</main>

<footer class="footer">
    <a href="index.php" class="footer-brand">✦ Tam-Bayan ng QCU</a>
    <p>Made for QCU students, one vacant hour at a time.</p>
</footer>

</body>
</html>
<?php
$stmt->close();
$productStmt->close();
$conn->close();
?>