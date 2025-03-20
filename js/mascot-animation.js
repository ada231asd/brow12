document.addEventListener('DOMContentLoaded', () => {
    const speechBubble = document.querySelector('.speech-bubble');
    const typingText = document.querySelector('.typing-text');

    // Анимация расширения высоты блока
    setTimeout(() => {
        speechBubble.style.whiteSpace = 'normal';
        speechBubble.style.height = `${typingText.scrollHeight}px`;
    }, 4000); // Задержка для завершения анимации печати
});