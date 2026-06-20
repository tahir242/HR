$(document).ready(function () {
    $(".btn-details").on("click", function (e) {
        e.preventDefault();
        let url = $(this).data("url");
        window.location.href = url;
    });
});