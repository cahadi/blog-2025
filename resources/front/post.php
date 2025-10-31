<?php
/**
 * Шаблон отдельного поста
 */
?>
<article class="post-single">
    <header class="post-header">
        <h1><?= htmlspecialchars($post['meta']['title'] ?? 'Без названия') ?></h1>

        <?php if (!empty($post['meta']['cover_image'])): ?>
            <div class="post-cover">
                <img src="<?= htmlspecialchars($post['meta']['cover_image']) ?>"
                     alt="<?= htmlspecialchars($post['meta']['title'] ?? '') ?>"
                     class="post-cover-image">
            </div>
        <?php endif; ?>

        <div class="post-meta">
            <?php if (!empty($post['meta']['author'])): ?>
                <span class="post-author">Автор: <?= htmlspecialchars($post['meta']['author']) ?></span>
            <?php endif; ?>

            <?php if (!empty($post['meta']['date'])): ?>
                <span class="post-date"><?= htmlspecialchars($post['meta']['date']) ?></span>
            <?php endif; ?>
        </div>
    </header>

    <div class="post-content">
        <?= $post['body'] ?? '' ?>
    </div>
</article>