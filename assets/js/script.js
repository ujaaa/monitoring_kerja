// script.js — Monitoring Kerja
// Semua JavaScript aplikasi disatukan di sini


document.addEventListener("DOMContentLoaded", function () {


    /* ==========================================================
       SUBMENU "TASK" DI SIDEBAR
    ========================================================== */

    const btnTaskToggle =
        document.querySelector("#btnTaskToggle");

    const taskSubmenu =
        document.querySelector("#taskSubmenu");

    const taskArrow =
        document.querySelector("#taskArrow");


    if (btnTaskToggle && taskSubmenu) {

        btnTaskToggle.addEventListener(
            "click",
            function () {

                taskSubmenu.classList.toggle("show");

                if (taskArrow) {

                    taskArrow.classList.toggle("rotate");

                }

            }
        );

    }



    /* ==========================================================
       DROPDOWN PROFIL DI TOPBAR
    ========================================================== */

    const btnProfile =
        document.querySelector("#btnProfile");

    const profileMenu =
        document.querySelector("#profileMenu");


    if (btnProfile && profileMenu) {

        btnProfile.addEventListener(
            "click",
            function (e) {

                e.stopPropagation();

                profileMenu.classList.toggle("show");

                btnProfile.setAttribute(
                    "aria-expanded",
                    profileMenu.classList.contains("show") ? "true" : "false"
                );

            }
        );


        document.addEventListener(
            "click",
            function (e) {

                if (
                    !profileMenu.contains(e.target) &&
                    !btnProfile.contains(e.target)
                ) {

                    profileMenu.classList.remove("show");
                    btnProfile.setAttribute("aria-expanded", "false");

                }

            }
        );

    }



    /* ==========================================================
       MODAL "PROFIL SAYA"
    ========================================================== */

    const overlay =
        document.querySelector("#profileModalOverlay");

    const btnOpen =
        document.querySelector("#btnOpenProfileModal");

    const btnClose =
        document.querySelector("#btnCloseProfileModal");

    const btnClose2 =
        document.querySelector("#btnCloseProfileModal2");


    if (overlay && btnOpen) {

        btnOpen.addEventListener(
            "click",
            function () {

                overlay.classList.add("show");


                if (profileMenu) {

                    profileMenu.classList.remove(
                        "show"
                    );

                }

            }
        );


        [btnClose, btnClose2].forEach(
            function (btn) {

                if (btn) {

                    btn.addEventListener(
                        "click",
                        function () {

                            overlay.classList.remove(
                                "show"
                            );

                        }
                    );

                }

            }
        );


        overlay.addEventListener(
            "click",
            function (e) {

                if (e.target === overlay) {

                    overlay.classList.remove(
                        "show"
                    );

                }

            }
        );


        document.addEventListener(
            "keydown",
            function (e) {

                if (e.key === "Escape") {

                    overlay.classList.remove(
                        "show"
                    );

                }

            }
        );

    }



    /* ==========================================================
       MODAL "TAMBAH TASK"
       Dipakai di task.php
    ========================================================== */

    const modalTask =
        document.querySelector("#modalTambahTask");

    const btnBukaTask =
        document.querySelector("#btnTambahKerjaan");

    const btnTutupTask =
        document.querySelector("#btnTutupModal");


    if (modalTask && btnBukaTask) {

        btnBukaTask.addEventListener(
            "click",
            function () {

                modalTask.classList.add("open");

            }
        );


        if (btnTutupTask) {

            btnTutupTask.addEventListener(
                "click",
                function () {

                    modalTask.classList.remove(
                        "open"
                    );

                }
            );

        }


        modalTask.addEventListener(
            "click",
            function (e) {

                if (e.target === modalTask) {

                    modalTask.classList.remove(
                        "open"
                    );

                }

            }
        );

    }



    /* ==========================================================
       MODAL "TAMBAH USER"
       Dipakai di users.php
    ========================================================== */

    const modalUser =
        document.querySelector("#modalTambahUser");

    const btnTambahUser =
        document.querySelector("#btnTambahUser");

    const btnTutupUser =
        document.querySelector("#btnTutupModal");

    const btnBatalUser =
        document.querySelector("#btnBatal");


    /* BUKA MODAL TAMBAH USER */

    if (modalUser && btnTambahUser) {

        btnTambahUser.addEventListener(
            "click",
            function () {

                modalUser.classList.add("show");

            }
        );

    }


    /* TUTUP MODAL DENGAN X */

    if (modalUser && btnTutupUser) {

        btnTutupUser.addEventListener(
            "click",
            function () {

                modalUser.classList.remove(
                    "show"
                );

            }
        );

    }


    /* TUTUP MODAL DENGAN BATAL */

    if (modalUser && btnBatalUser) {

        btnBatalUser.addEventListener(
            "click",
            function () {

                modalUser.classList.remove(
                    "show"
                );

            }
        );

    }


    /* KLIK DI LUAR MODAL */

    if (modalUser) {

        modalUser.addEventListener(
            "click",
            function (e) {

                if (e.target === modalUser) {

                    modalUser.classList.remove(
                        "show"
                    );

                }

            }
        );

    }


    /* TEKAN ESC UNTUK MENUTUP MODAL USER */

    document.addEventListener(
        "keydown",
        function (e) {

            if (e.key === "Escape") {

                if (modalUser) {

                    modalUser.classList.remove(
                        "show"
                    );

                }

            }

        }
    );


});