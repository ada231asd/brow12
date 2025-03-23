<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.0/nouislider.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<!-- <?php require '../Header/header.html'; ?> -->
<div class="catalog">
    <div class="filter_cont">

    <div class="filter-group">
        <div class="rating-filter">
            <span>Рейтинг:</span>
            <button class="rating-btn" data-rating="4">★★★★ и выше</button>
            <button class="rating-btn" data-rating="3">★★★ и выше</button>
            <button class="rating-btn" data-rating="0">Любой</button>
        </div>
    </div>
    <div class="categories-filter">
            <h3>Категории</h3>
            <div class="categories-list" id="categoriesContainer"></div>
        </div>
        <!-- Добавьте кнопку в HTML -->
<div class="price-filter">
    <h3>Фильтр по цене</h3>
    <div class="price-inputs">
        <input type="number" id="minPriceInput" placeholder="Мин" min="0" max="999999">
        <input type="number" id="maxPriceInput" placeholder="Макс" min="0" max="999999">
        <button id="applyPriceBtn">Применить</button>
    </div>
    <div class="price-slider-container">
        <div id="priceSlider"></div>
        <div class="price-labels">
            <span id="minPriceLabel">0 ₽</span>
            <span id="maxPriceLabel">999 999 ₽</span>
        </div>
    </div>
</div>
    </div>
     <div class="top_f">
     <div class="search-container">
    <input 
        type="text" 
        id="searchInput" 
        placeholder="Поиск товаров..." 
        class="search-input"
    >
    <div id="searchStatus" class="search-status"></div>
</div>
        <div class="fl_top">
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
     </div>   
</div>




<!-- <?php require '../Foter/foter.html';?> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.0/nouislider.min.js"></script>
<script src="js/script.js"></script>
</body>

</html>