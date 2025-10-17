        <!-- Основной контент -->
        <main class="col ms-sm-auto px-4 py-4 bg-light rounded-3">
            <h1 class="mb-4">Наши товары</h1>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($articles as $article) { ?>
                <div class="col">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <img src="https://via.placeholder.com/300x200" class="card-img-top rounded-top" alt="Зеленый чай" />
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <h5 class="card-title text-success"><?php echo $article['title']?></h5>
                                <p class="card-text"><?php echo $article['description'] ?></p>
                                <p class="fw-bold fs-5 mb-2"><?php echo $article['description'] ?> Цена</p>
                                <a href="#" class="btn btn-outline-primary w-100">Купить</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php };?>
            </div>
        </main>
    </div>
</div>