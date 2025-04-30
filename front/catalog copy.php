
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.0/nouislider.min.css">
    <script src="js/auth-guard.js"></script>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
}

.page-btn, .prev-btn, .next-btn {
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background-color: #fff;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.2s, color 0.2s;
}

.page-btn.active {
    background-color: #8A33FD;
    color: white;
    border-color: #8A33FD;
}

.page-btn:hover, .prev-btn:hover, .next-btn:hover {
    background-color: #f0f0f0;
}

.prev-btn.disabled, .next-btn.disabled {
    background-color: #e0e0e0;
    cursor: not-allowed;
    border-color: #e0e0e0;
}
.notification-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .notification {
            padding: 10px 20px;
            margin-bottom: 10px;
            border-radius: 5px;
            color: #fff;
        }

        .notification.success {
            background-color: #141414;
        }

        .notification.error {
            background-color: #141414;
        }

        .notification.info {
            background-color: #141414;
        }
    </style>
</head>
<body>
<div id="notificationContainer" class="notification-container"></div>
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
 <div id="paginationContainer" class="pagination"></div>
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