document.addEventListener("DOMContentLoaded", () => {
    const fadeElements = document.querySelectorAll(".fade-in");

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("visible");
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2 });

    fadeElements.forEach(el => observer.observe(el));
});

document.addEventListener("DOMContentLoaded", function () {
    const headers = document.querySelectorAll(".accordion-header");

    headers.forEach(header => {
        header.addEventListener("click", function () {
            // Закрыть все
            headers.forEach(h => {
                if (h !== this) {
                    h.classList.remove("active");
                    h.nextElementSibling.classList.remove("open");
                }
            });

            // Переключить текущий
            this.classList.toggle("active");
            this.nextElementSibling.classList.toggle("open");
        });
    });
});

