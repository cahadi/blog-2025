<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($meta['title'] ?? 'Мой сайт') ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta['description'] ?? '') ?>">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="container">
    <header class="header">
        <h1>Мой сайт</h1>
        <nav class="nav">
            <a href="/">Главная</a>
            <a href="/articles">Статьи</a>
            <a href="/calc">Калькулятор</a>
            <a href="/category/coding">Программирование</a>
        </nav>
    </header>

    <div class="content-wrapper">
        <aside class="sidebar">
            <h3>Категории</h3>
            <ul class="categories-list">
                <?php foreach ($categories as $category): ?>
                    <li class="<?= ($currentCategory ?? '') === $category ? 'active' : '' ?>">
                        <a href="/category/<?= htmlspecialchars($category) ?>">
                            <?= htmlspecialchars($category) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <main class="main-content">
            <h2>Посты в категории "<?= htmlspecialchars($currentCategory ?? 'Все категории') ?>"</h2>

            <?php if (empty($posts)): ?>
                <div class="no-posts">
                    <p>В этой категории пока нет постов.</p>
                </div>
            <?php else: ?>
                <div class="posts-grid">
                    <?php foreach ($posts as $post): ?>
                        <article class="post-card">
                            <?php if (!empty($post['meta']['cover_image'])): ?>
                                <div class="post-image">
                                    <img src="<?= htmlspecialchars($post['meta']['cover_image']) ?>"
                                         alt="<?= htmlspecialchars($post['meta']['title'] ?? '') ?>"
                                         class="post-thumbnail">
                                </div>
                            <?php endif; ?>

                            <div class="post-body">
                                <h3 class="post-title">
                                    <?= htmlspecialchars($post['meta']['title'] ?? 'Без названия') ?>
                                </h3>

                                <p class="post-description">
                                    <?= htmlspecialchars($post['meta']['description'] ?? '') ?>
                                </p>

                                <div class="post-meta">
                                    <?php if (!empty($post['meta']['author'])): ?>
                                        <span class="post-author"><?= htmlspecialchars($post['meta']['author']) ?></span>
                                    <?php endif; ?>

                                    <a href="/post/<?= basename($post['meta']['slug'] ?? '') ?>"
                                       class="post-link">
                                        Читать далее...
                                    </a>
                                </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Мой сайт. Все права защищены.</p>
    </footer>
</div>

</body>
</html>