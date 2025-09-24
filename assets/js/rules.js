document.addEventListener("DOMContentLoaded", () => {
    const pdfViewer = document.getElementById("rules-pdf-viewer");

    function loadPDF(file, selector, activeClass) {
        pdfViewer.src = file;
        document.querySelectorAll(selector).forEach(btn => btn.classList.remove(activeClass));
        const clickedBtn = document.querySelector(`${selector}[data-pdf="${file}"]`);
        if (clickedBtn) clickedBtn.classList.add(activeClass);
    }

    // Обработчики для табов
    document.querySelectorAll(".tab-link").forEach(tab => {
        tab.addEventListener("click", () => {
            loadPDF(tab.dataset.pdf, ".tab-link", "active");
        });
    });

    // Обработчики для кнопок
    document.querySelectorAll(".rule-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            loadPDF(btn.dataset.pdf, ".rule-btn", "active");
        });
    });
});
