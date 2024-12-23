$(document).ready(function () {
    /* Remove alert on close button click */
    $('.close').on('click', function () {
        $('.alert').remove();
    });

    /* Form navigation */
    const prevBtns = $(".btn-prev");
    const nextBtns = $(".btn-next");
    const progress = $("#progress");
    const formSteps = $(".form-step");
    const progressSteps = $(".progress-step");

    let formStepsNum = 0;

    function updateFormSteps() {
        formSteps.removeClass("active");
        formSteps.eq(formStepsNum).addClass("active");
    }

    function updateProgressBar() {
        progressSteps.removeClass("active").slice(0, formStepsNum + 1).addClass("active");
        progress.width(((formStepsNum) / (progressSteps.length - 1)) * 100 + "%");
    }

    nextBtns.on('click', function () {
        formStepsNum++;
        updateFormSteps();
        updateProgressBar();
    });

    prevBtns.on('click', function () {
        formStepsNum--;
        updateFormSteps();
        updateProgressBar();
    });

    /* Set active class in navigation menu */
    const path = window.location.href;
    $("header ul a").each(function () {
        if (this.href === path) {
            $(this).addClass("active");
        }
    });

    /* Add path as class to body for styling */
    const pathClass = window.location.pathname.replace(/\//g, "_").replace(/^_/, "");
    $("body").addClass(pathClass);

    /* Fade in/out switch account dropdown */
    $(".right_nav ul .acc i").on('click', function () {
        $(".right_nav ul .acc .switch_acc").fadeIn();
    });

    $(".switch_acc .switch_head .btn-close").on('click', function () {
        $(".right_nav ul .acc .switch_acc").fadeOut();
    });

    /* Toggle message filter box */
    $(".messages_box a.message_filter").on('click', function () {
        $(".messages_box .msg_filter_cont").fadeToggle("slow");
    });

    /* Sidebar active class on current page */
    $(".sidebar_menu ul a").each(function () {
        if (this.href === path) {
            $(this).addClass("active");
        }
    });

    /* Dark mode toggle */
    if (sessionStorage.getItem("lightMode") === "enabled") {
        enableDarkMode();
    }

    $("#darkModeToggle").on("click", function () {
        if ($("body").hasClass("light-mode")) {
            disableDarkMode();
        } else {
            enableDarkMode();
        }
    });

    function enableDarkMode() {
        $("body").addClass("light-mode");
        sessionStorage.setItem("lightMode", "enabled");
    }

    function disableDarkMode() {
        $("body").removeClass("light-mode");
        sessionStorage.removeItem("lightMode");
    }
});
