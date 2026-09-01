document.addEventListener("DOMContentLoaded", function () {

    const inputFoto =
        document.getElementById("inputFoto");

    const previewFoto =
        document.getElementById("previewFoto");


    if (!inputFoto || !previewFoto) {
        return;
    }


    inputFoto.addEventListener("change", function () {

        const file = this.files[0];


        if (!file) {
            return;
        }


        /* ==================================================
           CEK UKURAN
        ================================================== */

        if (file.size > 2 * 1024 * 1024) {

            alert("Ukuran foto maksimal 2 MB.");

            this.value = "";

            return;
        }


        /* ==================================================
           CEK FORMAT
        ================================================== */

        const allowedTypes = [
            "image/jpeg",
            "image/png",
            "image/webp"
        ];


        if (!allowedTypes.includes(file.type)) {

            alert(
                "Format foto harus JPG, PNG, atau WEBP."
            );

            this.value = "";

            return;
        }


        /* ==================================================
           PREVIEW FOTO
        ================================================== */

        const reader =
            new FileReader();


        reader.onload = function (e) {


            if (
                previewFoto.tagName.toLowerCase()
                === "img"
            ) {

                previewFoto.src =
                    e.target.result;

            }

            else {


                const img =
                    document.createElement("img");


                img.src =
                    e.target.result;


                img.alt =
                    "Preview Foto";


                img.id =
                    "previewFoto";


                img.className =
                    "profile-photo";


                previewFoto.replaceWith(img);

            }

        };


        reader.readAsDataURL(file);

    });

});