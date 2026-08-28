<?php
require __DIR__ . '/includes/icons.php';

$navLinks = [
    ['href' => '#home',          'label' => 'Home',          'active' => true],
    ['href' => '#about',         'label' => 'About Us',      'active' => false],
    ['href' => '#services',      'label' => 'Services',      'active' => false],
    ['href' => '#announcements', 'label' => 'Announcements', 'active' => false],
    ['href' => '#calendar',      'label' => 'Calendar',      'active' => false],
    ['href' => '#contact',       'label' => 'Contact',       'active' => false],
];

$features = [
    ['icon' => 'praying-hands', 'title' => 'Our Faith',         'desc' => 'Centered on the Holy Eucharist and the teachings of Jesus Christ.'],
    ['icon' => 'people',        'title' => 'Our Community',     'desc' => 'A loving and supportive community journeying together in faith.'],
    ['icon' => 'heart-cross',   'title' => 'Our Mission',       'desc' => 'To proclaim the Gospel and to serve all with love and compassion.'],
    ['icon' => 'bible',         'title' => 'Sacraments',        'desc' => "We celebrate the sacraments and guide faithful through life's spiritual journey."],
    ['icon' => 'heart-hand',    'title' => 'Serve Others',      'desc' => 'We are called to serve our brothers and sisters through acts of love and charity.'],
    ['icon' => 'church',        'title' => 'Growing Together',  'desc' => 'Walking together as one family, building a stronger parish community.'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Our Lady of the Gate Parish · ParishServe</title>
<meta name="description" content="Our Lady of the Gate Parish -- stay connected with parish services, announcements, and the parish calendar through ParishServe.">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>

<a href="#home" class="pub-skip-link">Skip to main content</a>

<header class="pub-navbar">
    <div class="pub-navbar-inner">

        <a href="#home" class="pub-brand">
            <span class="pub-brand-crest"><?php ps_icon('crest'); ?></span>
            <span class="pub-brand-text">
                <strong>Our Lady<br>of the Gate Parish</strong>
                <em>ParishServe</em>
            </span>
        </a>

        <nav class="pub-nav" aria-label="Primary">
            <?php foreach ($navLinks as $link): ?>
                <a href="<?php echo htmlspecialchars($link['href']); ?>" class="pub-nav-link<?php echo $link['active'] ? ' active' : ''; ?>"<?php echo $link['active'] ? ' aria-current="page"' : ''; ?>>
                    <?php echo htmlspecialchars($link['label']); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="pub-navbar-actions">
            <a href="login.php" class="pub-btn-login"><?php ps_icon('user'); ?> Log In</a>
            <a href="register.php" class="pub-btn-getstarted">Get Started</a>
        </div>

        <button type="button" class="pub-menu-toggle" data-mobile-menu-toggle aria-expanded="false" aria-controls="pubMobileMenu" aria-label="Toggle menu">
            <?php ps_icon('menu', 'pub-menu-icon-open'); ?>
            <?php ps_icon('close', 'pub-menu-icon-close'); ?>
        </button>
    </div>

    <div class="pub-mobile-menu" id="pubMobileMenu" data-mobile-menu hidden>
        <?php foreach ($navLinks as $link): ?>
            <a href="<?php echo htmlspecialchars($link['href']); ?>" class="pub-mobile-link<?php echo $link['active'] ? ' active' : ''; ?>">
                <?php echo htmlspecialchars($link['label']); ?>
            </a>
        <?php endforeach; ?>
        <div class="pub-mobile-actions">
            <a href="login.php" class="pub-btn-login"><?php ps_icon('user'); ?> Log In</a>
            <a href="register.php" class="pub-btn-getstarted">Get Started</a>
        </div>
    </div>
</header>

<main id="main">

    <!-- ============================ HOME ============================ -->
    <section class="pub-hero" id="home">
        <div class="pub-hero-media">
            <img src="assets/images/parish-hero.svg" alt="Our Lady of the Gate Parish church at golden hour, with its bell tower and cross">
        </div>

        <div class="pub-hero-inner">
            <div class="pub-hero-text">
                <span class="pub-eyebrow">WELCOME TO</span>
                <h1>Our Lady<br>of the Gate<br>Parish</h1>
                <div class="ps-heading-ornament"><span></span><?php ps_icon('cross'); ?><span></span></div>
                <p class="pub-hero-lede">A community of faith, hope, and love.</p>
                <p class="pub-hero-body">Stay connected with our parish community, discover parish services, keep up with announcements, and find upcoming activities and celebrations.</p>

                <div class="pub-hero-ctas">
                    <a href="#services" class="ps-btn ps-btn-primary"><?php ps_icon('praying-hands'); ?> Explore Parish Services <?php ps_icon('arrow-right'); ?></a>
                    <a href="#calendar" class="ps-btn ps-btn-outline"><?php ps_icon('calendar'); ?> View Parish Calendar</a>
                </div>
            </div>
        </div>
    </section>

    <div class="pub-feature-strip">
        <div class="pub-feature-strip-inner">
            <?php foreach ($features as $f): ?>
                <div class="pub-feature">
                    <span class="pub-feature-icon"><?php ps_icon($f['icon']); ?></span>
                    <strong><?php echo htmlspecialchars($f['title']); ?></strong>
                    <p><?php echo htmlspecialchars($f['desc']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</main>

<script src="assets/js/main.js"></script>
</body>
</html>
