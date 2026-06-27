document.addEventListener('DOMContentLoaded', () => {
    const regForm = document.getElementById('regForm');
    const globalError = document.getElementById('globalError');

    if (regForm) {
        regForm.onsubmit = async function(e) {
            e.preventDefault(); 
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const username = regForm.querySelector('[name="username"]').value;
            const email = regForm.querySelector('[name="email"]').value;

            globalError.style.display = 'none';
            globalError.innerHTML = '';

            // 1. Проверка на пробелы
            if (password.indexOf(' ') !== -1) {
                showError("❌ Пароль не должен содержать пробелы!");
                return false;
            }

            // 2. Проверка на длину (минимум 6 символов)
            if (password.length < 6) {
                showError("❌ Пароль должен быть не короче 6 символов!");
                return false;
            }

            // 3. Проверка совпадения паролей
            if (password !== confirmPassword) {
                showError("❌ Пароли не совпадают!");
                return false;
            }

            // 4. Проверка ника и почты через AJAX (оставляем как было)
            try {
                const formData = new FormData();
                formData.append('username', username);
                formData.append('email', email);

                const response = await fetch('admin/check_user.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.username_exists) {
                    showError("❌ Этот Никнейм уже занят!");
                    return false;
                }

                if (result.email_exists) {
                    showError("❌ Этот Email уже зарегистрирован!");
                    return false;
                }

                // Если всё идеально — отправляем форму
                regForm.submit();

            } catch (error) {
                console.error("Ошибка:", error);
                showError("⚠️ Ошибка сервера. Попробуйте еще раз.");
            }
        };
    }

    function showError(text) {
        globalError.innerHTML = text;
        globalError.style.display = 'block';
    }
});

function toggleTheme() {
    const htmlElement = document.documentElement;
    const currentTheme = htmlElement.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    htmlElement.setAttribute('data-bs-theme', newTheme);
    
    // Меняем иконку (опционально)
    const btn = document.getElementById('themeToggler');
    btn.innerHTML = newTheme === 'dark' ? '🌙' : '☀️';
}

function applyInitialTheme() {
    // Проверяем, сохранял ли пользователь тему ранее
    const savedTheme = localStorage.getItem('theme');
    
    if (savedTheme) {
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
    } else {
        // Если нет сохраненной, проверяем системные настройки
        const userPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = userPrefersDark ? 'dark' : 'light';
        document.documentElement.setAttribute('data-bs-theme', theme);
    }
    updateTogglerIcon();
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    document.documentElement.setAttribute('data-bs-theme', newTheme);
    localStorage.setItem('theme', newTheme); // Запоминаем выбор
    updateTogglerIcon();
}

function updateTogglerIcon() {
    const theme = document.documentElement.getAttribute('data-bs-theme');
    const btn = document.getElementById('themeToggler');
    if (btn) {
        btn.innerHTML = theme === 'dark' ? '🌙' : '☀️';
    }
}

// Запускаем при загрузке
document.addEventListener('DOMContentLoaded', applyInitialTheme);

const myCarousel = document.querySelector('#carouselExampleSlidesOnly')
const carousel = new bootstrap.Carousel(myCarousel, {
  interval: 5000, // менять слайд каждые 5 секунд
  ride: 'carousel'
})

// Инициализация обоих каруселей
var textCarousel = document.querySelector('#gameInfoCarousel');
var imageCarousel = document.querySelector('#imageCarousel');

var textC = new bootstrap.Carousel(textCarousel);
var imageC = new bootstrap.Carousel(imageCarousel);

// Когда меняется картинка — меняем текст
imageCarousel.addEventListener('slide.bs.carousel', function (event) {
    textC.to(event.to);
});

// Ждем загрузки DOM
document.addEventListener('DOMContentLoaded', function () {
    const textCarousel = document.querySelector('#gameInfoCarousel');
    const imageCarousel = document.querySelector('#imageCarousel');

    // Создаем экземпляры Bootstrap Carousel
    const textBS = new bootstrap.Carousel(textCarousel);
    const imageBS = new bootstrap.Carousel(imageCarousel);

    // Когда начинает крутиться текстовая карусель...
    textCarousel.addEventListener('slide.bs.carousel', function (event) {
        // ...переключаем картинку на тот же индекс
        imageBS.to(event.to);
    });

    // На всякий случай делаем обратную связь (если нажать на стрелки картинок)
    imageCarousel.addEventListener('slide.bs.carousel', function (event) {
        textBS.to(event.to);
    });
});

// Функция применения темы (должна быть в начале)
function applySavedTheme() {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
}

// Обработка формы входа
const loginForm = document.getElementById('loginForm');
const loginError = document.getElementById('loginError');

if (loginForm) {
    loginForm.onsubmit = function() {
        // Очищаем прошлые ошибки перед попыткой входа
        if (loginError) {
            loginError.style.display = 'none';
        }
        // Здесь мы просто позволяем форме отправиться, 
        // так как основная проверка (пароль/логин) идет на сервере
        return true; 
    };
}