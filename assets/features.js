document.addEventListener('DOMContentLoaded', () => {

    /* ==========================================
       PROMO TIMER
       ========================================== */
    const timerElement = document.querySelector('.promo-timer');
    if (timerElement) {
        const endDateStr = timerElement.getAttribute('data-end');
        // Попытка парсинга даты (YYYY-MM-DD HH:MM:SS)
        // Заменяем пробел на 'T' для совместимости с Safari/iOS
        const endDate = new Date(endDateStr.replace(' ', 'T')).getTime();

        const updateTimer = () => {
            const now = new Date().getTime();
            const distance = endDate - now;

            if (distance < 0) {
                timerElement.style.display = 'none';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            timerElement.querySelector('.t-days').innerText = days.toString().padStart(2, '0');
            timerElement.querySelector('.t-hours').innerText = hours.toString().padStart(2, '0');
            timerElement.querySelector('.t-minutes').innerText = minutes.toString().padStart(2, '0');
            timerElement.querySelector('.t-seconds').innerText = seconds.toString().padStart(2, '0');
        };

        updateTimer(); // Запуск сразу
        setInterval(updateTimer, 1000); // Обновление каждую секунду
    }

    /* ==========================================
       STATS COUNTERS (ANIMATED)
       ========================================== */
    const statNumbers = document.querySelectorAll('.stat-number');
    let hasAnimatedStats = false;

    const animateStats = () => {
        statNumbers.forEach(stat => {
            const target = parseInt(stat.getAttribute('data-target'));
            const duration = 2000; // 2 секунды
            const stepTime = Math.abs(Math.floor(duration / target));
            let current = 0;
            
            // Если число очень большое, увеличиваем шаг
            const increment = target > 1000 ? Math.ceil(target / 100) : 1;

            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                stat.innerText = current.toLocaleString('ru-RU');
            }, Math.max(stepTime, 20)); // минимум 20мс на шаг для плавности
        });
    };

    /* ==========================================
       SCROLL ANIMATIONS (Intersection Observer)
       ========================================== */
    const animatedElements = document.querySelectorAll('[data-aos]');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                
                // Добавляем задержку, если есть data-aos-delay
                const delay = entry.target.getAttribute('data-aos-delay');
                if (delay) {
                    setTimeout(() => {
                        entry.target.classList.add('aos-animate');
                    }, parseInt(delay));
                } else {
                    entry.target.classList.add('aos-animate');
                }

                // Запуск анимации цифр, если доскроллили до статы
                if (entry.target.classList.contains('stat-card') && !hasAnimatedStats) {
                    hasAnimatedStats = true;
                    animateStats();
                }

                // Прекращаем наблюдение после анимации
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1 // Срабатывает, когда 10% элемента видно
    });

    animatedElements.forEach(el => observer.observe(el));

    /* ==========================================
       FAQ ACCORDION
       ========================================== */
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');
        
        question.addEventListener('click', () => {
            const isActive = item.classList.contains('active');
            
            // Закрываем все остальные (опционально)
            faqItems.forEach(otherItem => {
                otherItem.classList.remove('active');
                otherItem.querySelector('.faq-answer').style.maxHeight = null;
            });

            if (!isActive) {
                item.classList.add('active');
                answer.style.maxHeight = answer.scrollHeight + "px";
            }
        });
    });

    /* ==========================================
       WINTER THEME - DYNAMIC SNOW SYSTEM
       ========================================== */
    if (document.body.classList.contains('new-year')) {
        const snowContainer = document.createElement('div');
        snowContainer.id = 'snow-container';
        document.body.appendChild(snowContainer);

        const createSnowflake = () => {
            const snowflake = document.createElement('div');
            snowflake.classList.add('snowflake');
            snowflake.innerHTML = '&#10052;';
            snowflake.style.left = Math.random() * 100 + 'vw';
            snowflake.style.animationDuration = Math.random() * 3 + 2.5 + 's';
            snowflake.style.opacity = Math.random() * 0.6 + 0.2;
            snowflake.style.fontSize = Math.random() * 12 + 8 + 'px';
            
            snowContainer.appendChild(snowflake);

            setTimeout(() => {
                snowflake.remove();
            }, 5500);
        };

        setInterval(createSnowflake, 150);
    }

});
