document.addEventListener("DOMContentLoaded", () => {
    const headers = document.querySelectorAll(".accordion-header");

    headers.forEach(header => {
        header.addEventListener("click", () => {
            const item = header.parentElement;
            const content = item.querySelector(".accordion-content");

            // Скрываем все
            document.querySelectorAll(".accordion-content").forEach(c => {
                if (c !== content) c.style.maxHeight = null;
            });
            document.querySelectorAll(".accordion-header").forEach(h => {
                if (h !== header) h.classList.remove("active");
            });

            // Переключаем текущий
            if (content.style.maxHeight) {
                content.style.maxHeight = null;
                header.classList.remove("active");
            } else {
                content.style.maxHeight = content.scrollHeight + "px";
                header.classList.add("active");
            }
        });
    });
});
