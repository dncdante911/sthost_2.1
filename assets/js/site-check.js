document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('siteCheckForm');
    const loader = document.getElementById('loader');
    const resultSection = document.getElementById('resultSection');
    const siteResult = document.getElementById('siteResult');

    // Функция для плавного удаления старых карточек
    function clearOldResults(callback) {
        const cards = document.querySelectorAll('.result-card.show');
        if (!cards.length) {
            callback();
            return;
        }
        let count = 0;
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.classList.remove('show');
                setTimeout(() => {
                    card.remove();
                    count++;
                    if (count === cards.length) {
                        callback();
                    }
                }, 400); // время, чтобы скрыться
            }, index * 100);
        });
    }

    // Функция для отрисовки карточек с анимацией
    function renderCards(cardsData) {
        siteResult.innerHTML = '';

        cardsData.forEach((cardHTML, index) => {
            const temp = document.createElement('div');
            temp.innerHTML = cardHTML.trim();
            const card = temp.firstChild;
            siteResult.appendChild(card);

            setTimeout(() => {
                card.classList.add('show');
            }, index * 150); // задержка между карточками
        });
    }

    // Обработчик формы
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Плавно убираем старые результаты перед новой проверкой
        clearOldResults(async () => {
            loader.style.display = 'block';
            resultSection.style.display = 'none';

            const formData = new FormData(form);

            try {
                const res = await fetch('/tools/api/site-checker.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                loader.style.display = 'none';

                let cards = [];

                if (data.error) {
                    cards.push(`<div class="result-card error"><h3>${data.error}</h3></div>`);
                } else {
                    let statusClass = 'warning';
                    if (data.status && data.status < 300) statusClass = 'success';
                    else if (!data.status || data.status >= 400) statusClass = 'error';

                    let sslInfo = '';
                    if (data.ssl) {
                        sslInfo = `
                            <p><strong>SSL:</strong> ${data.ssl.valid ? 'Дійсний' : 'Недійсний'} (${data.ssl.days_left} дн.)</p>
                            <p>Закінчується: ${data.ssl.expires}</p>
                        `;
                    }

                    // Можно разбить на отдельные карточки, если нужно
                    cards.push(`
                        <div class="result-card ${statusClass}">
                            <h3>${data.url}</h3>
                            <p><strong>HTTP статус:</strong> ${data.status}</p>
                            <p><strong>Час відповіді:</strong> ${data.response_time ?? '-'} мс</p>
                            ${sslInfo}
                        </div>
                    `);
                }

                renderCards(cards);
                resultSection.style.display = 'block';
            } catch (err) {
                loader.style.display = 'none';
                renderCards([
                    `<div class="result-card error"><h3>Помилка перевірки</h3><p>${err.message}</p></div>`
                ]);
                resultSection.style.display = 'block';
            }
        });
    });
});
