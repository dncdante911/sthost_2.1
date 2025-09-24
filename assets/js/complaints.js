document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("complaintForm");
    const status = document.getElementById("formStatus");

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        status.textContent = "Відправка...";
        status.className = "form-status";

        let formData = new FormData(form);

        try {
            let res = await fetch("/pages/info/send-complaint.php", {
                method: "POST",
                body: formData
            });
            let data = await res.json();

            if (data.success) {
                status.textContent = "✅ Повідомлення надіслано!";
                status.classList.add("success");
                form.reset();
            } else {
                status.textContent = "❌ Помилка: " + data.message;
                status.classList.add("error");
            }
        } catch (err) {
            status.textContent = "❌ Помилка з'єднання";
            status.classList.add("error");
        }
    });
});
