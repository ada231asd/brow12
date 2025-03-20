// Инициализация слайдера
document.addEventListener('DOMContentLoaded', function() {
    let currentIndex = 0;
    const slides = document.querySelectorAll('.slide');
    const points = document.querySelectorAll('.point');
    const totalSlides = slides.length;

    // Функция для показа слайда
    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        points.forEach(point => point.classList.remove('active'));
        
        slides[index].classList.add('active');
        points[index].classList.add('active');
    }

    // Функция для переключения на следующий слайд
    function nextSlide() {
        currentIndex = (currentIndex + 1) % totalSlides;
        showSlide(currentIndex);
    }

    // Автоматическое переключение каждые 5 секунд
    setInterval(nextSlide, 5000);

    // Инициализация первого слайда
    showSlide(0);
});