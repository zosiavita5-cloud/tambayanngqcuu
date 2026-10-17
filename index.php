<?php
require_once "db.php";

$search = trim($_GET["search"] ?? "");
$category = $_GET["category"] ?? "all";

$allowedCategories = ["all", "food", "study", "relax"];

if (!in_array($category, $allowedCategories, true)) {
    $category = "all";
}

$sql = "SELECT * FROM places WHERE 1=1";
$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (name LIKE ? OR address LIKE ? OR description LIKE ?)";
    $keyword = "%" . $search . "%";
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $types .= "sss";
}

if ($category !== "all") {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY name ASC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$places = $stmt->get_result();

function e($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

function showTime($time) {
    if (empty($time)) {
        return "Not listed";
    }

    return date("g:i A", strtotime($time));
}

function categoryLabel($category) {
    $labels = [
        "food" => "☕ Café & Food",
        "study" => "📚 Study Spot",
        "relax" => "🌷 Chill Spot"
    ];

    return $labels[$category] ?? "📍 Tambayan";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tam-Bayan ng QCU | Find Your Study Spot</title>
    <meta name="description"
          content="Discover cafés, study spots, and chill places near Quezon City University.">

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
        <a href="#home">Home</a>
        <a href="#tambayan">Explore</a>
        <a href="#about">About</a>
    </nav>
</header>

<main>
    <section class="hero" id="home">
        <span class="eyebrow">YOUR CAMPUS BREAK, REIMAGINED ✧</span>

        <h1>May vacant ka?<br>
            Find your <span>happy place.</span>
        </h1>

        <p>
            Looking for a cozy café, a quiet study spot, or somewhere
            to relax between classes? Discover your next tambayan near QCU.
        </p>

        <a href="#tambayan" class="btn btn-primary">
            Explore Tambayan <span>↗</span>
        </a>

        <div class="hero-decoration decoration-one">✧</div>
        <div class="hero-decoration decoration-two">✦</div>
    </section>

    <section class="explore-section" id="tambayan">
        <div class="section-heading">
            <span class="eyebrow">YOUR NEXT DESTINATION</span>
            <h2>Find your kind of <span>tambayan.</span></h2>
            <p>Discover places to eat, study, or simply unwind.</p>
        </div>

        <form action="index.php#tambayan" method="GET" class="search-form">
            <div class="search-input-wrap">
                <span>⌕</span>
                <input
                    type="search"
                    name="search"
                    placeholder="Search places, cafés, or locations..."
                    value="<?= e($search) ?>"
                >
            </div>

            <select name="category" aria-label="Filter by category">
                <option value="all" <?= $category === "all" ? "selected" : "" ?>>
                    All places
                </option>
                <option value="food" <?= $category === "food" ? "selected" : "" ?>>
                    Cafés & Food
                </option>
                <option value="study" <?= $category === "study" ? "selected" : "" ?>>
                    Study Spots
                </option>
                <option value="relax" <?= $category === "relax" ? "selected" : "" ?>>
                    Chill Spots
                </option>
            </select>

            <button type="submit" class="btn btn-primary">Search</button>

            <?php if ($search !== "" || $category !== "all"): ?>
                <a href="index.php#tambayan" class="clear-link">Clear</a>
            <?php endif; ?>
        </form>

        <div class="results-caption">
            <span>Places to explore</span>
            <span class="results-note">Made for your vacant hours ✿</span>
        </div>

        <div class="places-grid">
            <?php if ($places->num_rows > 0): ?>
                <?php while ($place = $places->fetch_assoc()): ?>
                    <article class="place-card">

                        <div class="place-image-wrap">
                            <?php if (!empty($place["image"])): ?>
                                <img
                                    class="place-image"
                                    src="images/<?= e(basename($place["image"])) ?>"
                                    alt="<?= e($place["name"]) ?>"
                                    loading="lazy"
                                >
                            <?php else: ?>
                                <div class="image-placeholder">
                                    <span>✧</span>
                                    <small>YOUR NEXT TAMBAYAN</small>
                                </div>
                            <?php endif; ?>

                            <span class="category-badge">
                                <?= e(categoryLabel($place["category"])) ?>
                            </span>
                        </div>

                        <div class="place-content">
                            <h3><?= e($place["name"]) ?></h3>

                            <p class="place-address">
                                <span>⌖</span>
                                <?= e($place["address"]) ?>
                            </p>

                            <p class="place-description">
                                <?= e($place["description"] ?: "A place to explore during your vacant hours.") ?>
                            </p>

                            <div class="place-info">
                                <div>
                                    <span class="info-label">OPENING HOURS</span>
                                    <strong>
                                        <?= e(showTime($place["opening_time"])) ?>
                                        –
                                        <?= e(showTime($place["closing_time"])) ?>
                                    </strong>
                                </div>

                                <div>
                                    <span class="info-label">WI-FI</span>
                                    <strong>
                                        <?php
                                        $wifi = $place["wifi_available"] ?? "unknown";

                                        if ($wifi === "yes") {
                                            echo "✓ Available";
                                        } elseif ($wifi === "no") {
                                            echo "✕ Not available";
                                        } else {
                                            echo "? Unconfirmed";
                                        }
                                        ?>
                                    </strong>
                                </div>
                            </div>

                            <div class="card-actions">
                                <a class="btn btn-primary"
                                   href="details.php?id=<?= (int)$place["id"] ?>">
                                    View Details <span>↗</span>
                                </a>

                                <?php if (!empty($place["map_url"])): ?>
                                    <a class="map-link"
                                       href="<?= e($place["map_url"]) ?>"
                                       target="_blank"
                                       rel="noopener noreferrer">
                                        View Map ↗
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>

            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">☁</div>
                    <h3>No places found just yet.</h3>
                    <p>Try another search term or choose a different category.</p>
                    <a href="index.php#tambayan" class="btn btn-primary">
                        View all places
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="about-section" id="about">
        <div class="about-symbol">✦</div>
        <span class="eyebrow">A LITTLE SPACE FOR QCU STUDENTS</span>
        <h2>More than just a place.<br><span>It's your break.</span></h2>

        <p>
            Tam-Bayan ng QCU helps students discover nearby cafés,
            places to study, and spots to relax during long vacant periods.
            Find a place that fits your time, budget, and mood.
        </p>

        <a href="#tambayan" class="text-link">Find your next tambayan →</a>
    </section>
</main>

<footer class="footer">
    <a href="index.php" class="footer-brand">✦ Tam-Bayan ng QCU</a>
    <p>Made for QCU students, one vacant hour at a time.</p>
    <small>Confirm current prices, opening hours, and Wi-Fi availability with each venue.</small>
</footer>

</body>
</html>
<?php
$stmt->close();
$conn->close();
?>