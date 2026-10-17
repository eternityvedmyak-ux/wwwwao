document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('interactive-background');
    if (!container) return;

    const items = [
        'diamond.png',
        'emerald.png',
        'apple.png',
        'golden_apple.png',
        'diamond_sword.png',
        'ender_pearl.png',
        'iron_ingot.png',
        'gold_ingot.png'
    ];
    const baseUrl = 'https://cdn.jsdelivr.net/gh/InventivetalentDev/minecraft-assets@1.20.1/assets/minecraft/textures/item/';
    
    const numItems = parseInt(container.dataset.count) || 40; // Количество предметов из конфига
    const elements = [];

    // Создаем элементы
    for (let i = 0; i < numItems; i++) {
        const item = document.createElement('div');
        item.classList.add('bg-item');
        
        // Случайный предмет
        const randomItem = items[Math.floor(Math.random() * items.length)];
        item.style.backgroundImage = `url(${baseUrl}${randomItem})`;
        
        // Случайный размер (от 16 до 70 px)
        const size = Math.random() * 54 + 16;
        item.style.width = `${size}px`;
        item.style.height = `${size}px`;
        item.style.imageRendering = 'pixelated'; // Чтобы пиксели не размывались
        
        // Глубина резкости (Blur) и видимость (Opacity)
        let blurVal = 0;
        let opacityVal = 0.2;
        
        if (size < 28) {
            // Маленькие = далеко, размытые
            blurVal = 3;
            opacityVal = 0.18;
        } else if (size > 55) {
            // Очень крупные = слишком близко к камере, легкий расфокус
            blurVal = 1.5;
            opacityVal = 0.22;
        } else {
            // Средние = в фокусе, четкие и заметные
            blurVal = 0;
            opacityVal = 0.38;
        }
        
        item.style.filter = blurVal > 0 ? `blur(${blurVal}px)` : 'none';
        item.style.opacity = opacityVal;
        
        // Случайная позиция (с отступами от краев, чтобы не обрезало)
        const x = Math.random() * 86 + 7; // от 7% до 93% ширины экрана
        const y = Math.random() * 86 + 7; // от 7% до 93% высоты экрана
        item.style.left = `${x}vw`;
        item.style.top = `${y}vh`;
        
        // Случайный поворот
        const rotation = Math.random() * 360;
        item.style.transform = `rotate(${rotation}deg)`;
        
        // Скорость параллакса зависит от размера (ближе = быстрее)
        const depth = (size / 70) * 1.8 + 0.4;
        item.dataset.depth = depth;
        item.dataset.baseX = x;
        item.dataset.baseY = y;
        item.dataset.rotation = rotation;
        
        container.appendChild(item);
        elements.push(item);
    }

    // Эффект параллакса при движении мыши
    document.addEventListener('mousemove', (e) => {
        const mouseX = e.clientX / window.innerWidth - 0.5;
        const mouseY = e.clientY / window.innerHeight - 0.5;

        elements.forEach(el => {
            const depth = parseFloat(el.dataset.depth);
            // Смещение зависит от глубины
            const moveX = mouseX * 100 * depth;
            const moveY = mouseY * 100 * depth;
            const rotation = el.dataset.rotation;
            
            // Плавное перемещение с сохранением вращения
            el.style.transform = `translate(${moveX}px, ${moveY}px) rotate(${rotation}deg)`;
        });
    });
});
