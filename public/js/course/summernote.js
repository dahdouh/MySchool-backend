$(document).ready(function () {
    $(".summernote").each(function () {
        $(this).summernote({
            height: 350,
            placeholder: $(this).attr("placeholder"),
            codemirror: {
                theme: 'paraiso-dark'
            }
        });
    });
});