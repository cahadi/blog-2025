<div class="home-page">
    <div class="jumbotron">
        <h1>Добро пожаловать в мир чая</h1>
        <p class="lead">Откройте для себя лучшие сорта чая и традиции чаепития</p>
    </div>

    <div class="featured-posts">
        <h2>Последние статьи</h2>
        <div class="posts-grid">
            <?php
            $featuredPosts = array_slice($posts ?? [], 0, 3);
            foreach ($featuredPosts as $post):
                ?>
                <div class="post-card">
                    <h3><?= htmlspecialchars($post['meta']['title'] ?? 'Без названия') ?></h3>
                    <p><?= htmlspecialchars($post['meta']['description'] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>