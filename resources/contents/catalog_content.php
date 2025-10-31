<!-- Основной контент -->
<main class="col ms-sm-auto px-4 py-4 bg-light rounded-3">
    <h1 class="mb-4">Наши товары</h1>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($articles as $article) { ?>
            <div class="col">
                <div class="card h-100 shadow-sm hover-shadow">
                    <img src="https://i.dailymail.co.uk/i/pix/2013/03/14/article-2293446-18A9F84E000005DC-46_1024x615_large.jpg" class="card-img-top rounded-top" alt="Зеленый чай" />
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title text-success"><?php echo htmlspecialchars($article['title'])?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($article['description']) ?></p>
                            <p class="fw-bold fs-5 mb-2">Цена: <?php echo htmlspecialchars($article['price']) ?> </p>
                            <a href="#" class="btn btn-outline-primary w-100">Купить</a>
                            <?php
                            $filePath = '../../../content/posts/tea.md'; // или другой путь, соответствующий структуре

                            if (isset($articleContents[$filePath])) {
                                echo '<div class="article-content">' . nl2br(htmlspecialchars($articleContents[$filePath])) . '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</main>
</div>
</div>