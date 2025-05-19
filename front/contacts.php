<?php
// Подключение к базе данных
$servername = "127.0.0.1:3306";
$username = "root";
$password = "";
$dbname = "pk_st";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4");
    
    // Запрос для получения 3 активных магазинов
    $stmt = $conn->prepare("SELECT store_id, name, address, phone, latitude, longitude, working_hours 
                            FROM Stores 
                            WHERE status = 'Активен' 
                            LIMIT 3");
    $stmt->execute();
    $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Ошибка подключения: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Контакты</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="css/contacts.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
<?php require '../Header/header.html'; ?>
   <div class="blok_konst">
    <h1>Контакты</h1>
    <div class="map_adr">
        <div class="stores-container">
            <?php foreach ($stores as $store): ?>
            <div class="store" 
                 onclick="zoomToStore(<?php echo htmlspecialchars($store['latitude']); ?>, 
                                     <?php echo htmlspecialchars($store['longitude']); ?>, 
                                     '<?php echo htmlspecialchars($store['name']); ?>')">
                <h3><?php echo htmlspecialchars($store['name']); ?></h3>
                <p><strong>Адрес:</strong> <?php echo htmlspecialchars($store['address']); ?></p>
                <p><strong>Телефон:</strong> <?php echo htmlspecialchars($store['phone']); ?></p>
                <p><strong>Часы работы:</strong> <?php echo htmlspecialchars($store['working_hours']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>

    <div id="map"></div>
    </div>
        <div class="block_ss">
            <h2>Связаться с нами</h2>
    <div class="contact-form-container">
        
        <form class="contact-form">
            <div class="n">
                <label for="name">Имя</label>
            <input type="text" id="name" name="name" placeholder="Введите ваше имя" required>
            
            <label for="phone">Телефон</label>
            <input type="tel" id="phone" name="phone" placeholder="Введите ваш телефон" required>
            
              <button type="submit">Отправить</button>
            </div>
            <div class="sob">
                <label for="message">Сообщение</label>
            <textarea id="message" name="message" placeholder="Введите ваше сообщение" required></textarea>
            </div>
            
            
        </form>
    </div>
   </div> 
        </div>

    
    
<?php require '../Foter/foter.html';?>
    <script>
        // Инициализация карты
        const map = L.map('map').setView([55.7558, 37.6173], 10); // Центр Москвы

        // Добавление тайлов OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Добавление маркеров магазинов
        const stores = [
            <?php foreach ($stores as $store): ?>
                {
                    lat: <?php echo htmlspecialchars($store['latitude']); ?>,
                    lng: <?php echo htmlspecialchars($store['longitude']); ?>,
                    name: "<?php echo htmlspecialchars($store['name']); ?>",
                    address: "<?php echo htmlspecialchars($store['address']); ?>",
                    phone: "<?php echo htmlspecialchars($store['phone']); ?>",
                    hours: "<?php echo htmlspecialchars($store['working_hours']); ?>"
                },
            <?php endforeach; ?>
        ];

        stores.forEach(store => {
            const marker = L.marker([store.lat, store.lng]).addTo(map);
            marker.bindPopup(`
                <b>${store.name}</b><br>
                Адрес: ${store.address}<br>
                Телефон: ${store.phone}<br>
                Часы работы: ${store.hours}
            `);
        });

        // Функция для увеличения масштаба при клике на магазин
        function zoomToStore(lat, lng, name) {
            map.setView([lat, lng], 16); // Увеличение до масштаба 16
            stores.forEach(store => {
                if (store.name === name) {
                    L.marker([store.lat, store.lng])
                        .addTo(map)
                        .bindPopup(`
                            <b>${store.name}</b><br>
                            Адрес: ${store.address}<br>
                            Телефон: ${store.phone}<br>
                            Часы работы: ${store.hours}
                        `)
                        .openPopup();
                }
            });
        }
    </script>
</body>
</html>