document.addEventListener("DOMContentLoaded", function () {

    const modal = document.getElementById("modalTambahTask");
    const btnTambah = document.getElementById("btnTambahKerjaan");
    const btnTutup = document.getElementById("btnTutupModal");


    // BUKA MODAL
    if (btnTambah) {

        btnTambah.addEventListener("click", function () {

            modal.classList.add("open");

            document.body.classList.add("modal-open");

        });

    }


    // TUTUP MODAL
    if (btnTutup) {

        btnTutup.addEventListener("click", function () {

            modal.classList.remove("open");

            document.body.classList.remove("modal-open");

        });

    }


    // KLIK DI LUAR MODAL
    if (modal) {

        modal.addEventListener("click", function (event) {

            if (event.target === modal) {

                modal.classList.remove("open");

                document.body.classList.remove("modal-open");

            }

        });

    }


    // TOMBOL ESC
    document.addEventListener("keydown", function (event) {

        if (event.key === "Escape") {

            if (modal && modal.classList.contains("open")) {

                modal.classList.remove("open");

                document.body.classList.remove("modal-open");

            }

        }

    });

});