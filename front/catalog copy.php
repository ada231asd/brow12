
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.0/nouislider.min.css">
    <script src="js/auth-guard.js"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php require '../Header/header.html'; ?>
<div class="catalog">
    <div class="filter_cont">

    <div class="filter-group">
    <div class="categories-filter">
            <h3>Категории:</h3>
            <div class="categories-list" id="categoriesContainer"></div>
        </div>
        <div class="rating-filter">
            <h3>Рейтинг:</h3>
            <div class="rating-btn" data-rating="4">★★★★ и выше</div>
            <div class="rating-btn" data-rating="3">★★★ и выше</div>
            <div class="rating-btn" data-rating="0">Любой</div>
        </div>
    </div>
</div>
    
     <div class="top_f">
     
        <div class="fl_top">
        <div class="search-container">
    <input 
        type="text" 
        id="searchInput" 
        placeholder="Поиск товаров..." 
        class="search-input"
    >
</div>
        <div class="filter-group">
            <button class="filter-btn" data-filter="hit">Хиты</button>
            <button class="filter-btn" data-filter="new">Новинки</button>
            <div class="rs" id="resetFilters">Сбросить всё</div>
        </div>
        <select class="sort-select" id="sortSelect">
        <option value="">Сортировка</option>
        <option value="price_asc">По возрастанию цены</option>
        <option value="price_desc">По убыванию цены</option>
    </select>
    </div>
 <div class="catalog-container" id="products-container"></div>
 <div class="loading-overlay" id="loadingOverlay" style="display: none;">
    <div class="loader"></div>
</div>
 <div class="no-results" id="noResults" style="display: none;">
    <div class="no-results-content">
        <div class="no-results-image">
           
        </div>
        <h3 class="no-results-title">Ничего не найдено</h3>
        <p class="no-results-text">Попробуйте изменить параметры поиска</p>
    </div>
</div>
     </div>   
</div>




<?php require '../Foter/foter.html';?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.0/nouislider.min.js"></script>
<script src="js/script.js"></script>
</body>

</html>