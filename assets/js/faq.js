document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("faqSearch");
    const faqItems = document.querySelectorAll(".faq-item");

    // Поиск по вопросам
    searchInput.addEventListener("input", () => {
        const term = searchInput.value.toLowerCase();
        faqItems.forEach(item => {
            const question = item.querySelector(".faq-question").textContent.toLowerCase();
            const answer = item.querySelector(".faq-answer").textContent.toLowerCase();
            item.style.display = question.includes(term) || answer.includes(term) ? "" : "none";
        });
    });

    // Аккордеон
    document.querySelectorAll(".faq-question").forEach(q => {
        q.addEventListener("click", () => {
            const answer = q.nextElementSibling;
            if (answer.style.maxHeight) {
                answer.style.maxHeight = null;
            } else {
                document.querySelectorAll(".faq-answer").forEach(a => a.style.maxHeight = null);
                answer.style.maxHeight = answer.scrollHeight + "px";
            }
        });
    });
});
