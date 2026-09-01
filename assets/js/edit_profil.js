document.addEventListener("DOMContentLoaded", function () {

    const fotoInput    = document.getElementById("fotoInput");
    const previewImg   = document.getElementById("previewImg");
    const previewInisial = document.getElementById("previewInisial");

    if (fotoInput) {

        fotoInput.addEventListener("change", function () {

            const file = this.files[0];

            if (!file) {
                return;
            }

            // Validasi ukuran maksimal 2MB di sisi browser juga
            if (file.size > 2 * 1024 * 1024) {
                alert("Ukuran foto maksimal 2MB.");
                this.value = "";
                return;
            }

            const reader = new FileReader();

            reader.onload = function (e) {

                previewImg.src = e.target.result;
                previewImg.style.display = "block";

                if (previewInisial) {
                    previewInisial.style.display = "none";
                }

            };

            reader.readAsDataURL(file);

        });

    }

});