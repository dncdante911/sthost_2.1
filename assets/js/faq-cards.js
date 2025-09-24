document.addEventListener("DOMContentLoaded", () => {
    const search = document.getElementById("faqSearch");
    const cards = document.querySelectorAll(".faq-card");

    search.addEventListener("input", () => {
        const term = search.value.toLowerCase();
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(term) ? "" : "none";
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("faqSearch");
    const cards = document.querySelectorAll(".faq-card");

    searchInput.addEventListener("input", function () {
        const query = this.value.toLowerCase();

        cards.forEach(card => {
            const text = card.innerText.toLowerCase();
            if (text.includes(query)) {
                card.style.display = "block";
            } else {
                card.style.display = "none";
            }
        });
    });
});
