document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('ipCheckForm');
    const loader = document.getElementById('loader');
    const resultSection = document.getElementById('resultSection');
    const resultDiv = document.getElementById('ipResult');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        loader.style.display = 'block';
        resultSection.style.display = 'none';
        resultDiv.innerHTML = '';

        const ipValue = document.getElementById('ipInput').value.trim();
        const formData = new FormData();
        formData.append('ip', ipValue);

        fetch('/pages/tools/api/ip-checker.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                loader.style.display = 'none';
                resultSection.style.display = 'block';

                if (data.error) {
                    resultDiv.innerHTML = `<div class="error-card">${data.error}</div>`;
                    return;
                }

                let html = '';
                for (let key in data) {
                    html += `<div class="result-card"><strong>${key}</strong>: ${data[key]}</div>`;
                }
                resultDiv.innerHTML = html;
            })
            .catch(err => {
                loader.style.display = 'none';
                resultDiv.innerHTML = `<div class="error-card">Помилка: ${err.message}</div>`;
                resultSection.style.display = 'block';
            });
    });
});
