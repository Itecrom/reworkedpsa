<?php
include 'includes/db.php';

$result = $conn->query('SELECT * FROM team_members ORDER BY category ASC, name ASC, id ASC');
$membersByCategory = [];
$categoryLabels = [
    'Patron' => 'Patron',
    'Members of the Executive' => 'Executive Committee',
    'Editorial Team' => 'Editorial Board',
    'Secretariat' => 'Secretariat',
    'Secretariate' => 'Secretariat',
    'executive' => 'Executive Committee',
    'board' => 'Board',
    'staff' => 'Secretariat',
    'other' => 'Other',
];

while ($member = $result->fetch_assoc()) {
    $category = trim((string)($member['category'] ?? 'Other'));
    if ($category === '') {
        $category = 'Other';
    }
    $membersByCategory[$category][] = $member;
}

$orderedCategories = ['Patron', 'Members of the Executive', 'executive', 'board', 'Editorial Team', 'Secretariat', 'Secretariate', 'staff', 'Other', 'other'];
$categoryKeys = array_merge(
    array_values(array_filter($orderedCategories, static function ($category) use ($membersByCategory) {
        return isset($membersByCategory[$category]);
    })),
    array_values(array_diff(array_keys($membersByCategory), $orderedCategories))
);

include 'includes/header.php';
?>

<?php renderPageTitle('Our Leadership and Team', [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'About Us', 'url' => '/about'],
    ['label' => 'Organisation Structure', 'url' => ''],
], 'images/background/page-title-2.jpg', 'Meet the people serving the Malawi Political Science Association.'); ?>

<section class="team-section section">
    <div class="container">
        <?php if (!$membersByCategory): ?>
            <div class="alert alert-info text-center">Team profiles will be published here soon.</div>
        <?php else: ?>
            <?php foreach ($categoryKeys as $category): ?>
                <section class="team-category mb-5" aria-labelledby="team-category-<?= (int)array_search($category, $categoryKeys, true) ?>">
                    <div class="team-section-heading">
                        <span class="section-title-sm">Malawi Political Science Association</span>
                        <h2 class="team-category-title" id="team-category-<?= (int)array_search($category, $categoryKeys, true) ?>">
                            <?= htmlspecialchars($categoryLabels[$category] ?? $category, ENT_QUOTES, 'UTF-8') ?>
                        </h2>
                    </div>
                    <div class="row">
                        <?php foreach ($membersByCategory[$category] as $member): ?>
                            <?php
                            $name = (string)($member['name'] ?? '');
                            $image = basename(str_replace('\\', '/', (string)($member['image'] ?? '')));
                            $imagePath = $image !== '' ? 'uploads/team/' . rawurlencode($image) : '';
                            $email = trim((string)($member['email'] ?? ''));
                            ?>
                            <div class="col-sm-6 col-lg-4 mb-4">
                                <article class="team-card h-100">
                                    <?php if ($imagePath !== ''): ?>
                                        <img src="<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" class="team-avatar" loading="lazy">
                                    <?php else: ?>
                                        <div class="team-avatar team-avatar-placeholder" aria-hidden="true"><?= htmlspecialchars(mb_strtoupper(mb_substr($name, 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>
                                    <h3><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h3>
                                    <p class="team-position"><?= htmlspecialchars((string)($member['position'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                                    <?php if (!empty($member['bio'])): ?>
                                        <p class="team-bio"><?= nl2br(htmlspecialchars((string)$member['bio'], ENT_QUOTES, 'UTF-8')) ?></p>
                                    <?php endif; ?>
                                    <div class="team-social">
                                        <?php if (filter_var($email, FILTER_VALIDATE_EMAIL)): ?>
                                            <a href="mailto:<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" aria-label="Email <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><i class="ti-email" aria-hidden="true"></i></a>
                                        <?php endif; ?>
                                        <?php foreach (['facebook' => 'Facebook', 'twitter' => 'X', 'linkedin' => 'LinkedIn'] as $field => $label): ?>
                                            <?php
                                            $socialUrl = trim((string)($member[$field] ?? ''));
                                            $socialScheme = strtolower((string)parse_url($socialUrl, PHP_URL_SCHEME));
                                            ?>
                                            <?php if ($socialUrl !== '' && in_array($socialScheme, ['http', 'https'], true)): ?>
                                                <a href="<?= htmlspecialchars($socialUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= $label ?> profile for <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
                                                    <i class="ti-<?= $field === 'twitter' ? 'twitter-alt' : $field ?>" aria-hidden="true"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
