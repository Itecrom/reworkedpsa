<?php

function renderPageTitle($title, $breadcrumbs, $background = 'images/background/page-title.jpg', $description = '') {
    $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeBackground = htmlspecialchars($background, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    ?>
    <section class="page-title overlay site-page-title" style="background-image: url('<?= $safeBackground ?>');">
        <div class="container text-center">
            <h1 class="text-white font-weight-bold"><?= $safeTitle ?></h1>
            <?php if ($description !== ''): ?>
                <p class="page-title-description text-white"><?= htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <?php endif; ?>
            <nav aria-label="Breadcrumb">
                <ol class="breadcrumb site-breadcrumb">
                    <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                        <?php $label = htmlspecialchars($breadcrumb['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                        <?php if (!empty($breadcrumb['url'])): ?>
                            <li class="breadcrumb-item"><a href="<?= htmlspecialchars($breadcrumb['url'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?= $label ?></a></li>
                        <?php else: ?>
                            <li class="breadcrumb-item active" aria-current="page"><?= $label ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
        </div>
    </section>
    <?php
}
