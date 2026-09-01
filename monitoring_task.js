// Filter berdasarkan status & user - reload halaman dengan query string gabungan
function filterTask() {

    const status = document.getElementById("filterStatus").value;
    const user   = document.getElementById("filterUser").value;

    const url = new URL(window.location.href);

    if (status === "") {
        url.searchParams.delete("status");
    } else {
        url.searchParams.set("status", status);
    }

    if (user === "") {
        url.searchParams.delete("user");
    } else {
        url.searchParams.set("user", user);
    }

    window.location.href = url.toString();

}


// Search realtime tanpa reload - cari di kolom nama pekerjaan & deskripsi
function searchTask() {

    const keyword = document
        .getElementById("searchTask")
        .value
        .toLowerCase();

    const rows = document
        .querySelectorAll("#taskTable tbody tr");

    rows.forEach(function (row) {

        const text = row.textContent.toLowerCase();

        if (text.includes(keyword)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }

    });

}


// Tutup dropdown profil kalau klik di luar
document.addEventListener("click", function (e) {

    const dropdown = document.getElementById("profileDropdown");
    const button = document.querySelector(".profile-button");

    if (!dropdown || !button) {
        return;
    }

    if (!dropdown.contains(e.target) && !button.contains(e.target)) {
        dropdown.classList.remove("show");
    }

});