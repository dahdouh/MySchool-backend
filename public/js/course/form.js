const $btnAddForm = $("#addForm");

/**
 * Add form
 */

// add an empty form card on button click
$btnAddForm.on("click", function () {
    $btnAddForm.hide();
    $("form.d-none").removeClass("d-none").hide().slideDown();
    isValidForm();
});

/**
 * Check validity when input are being edited
 */

$(document).on("change", ".card input, .card textarea", function () {
    isValidForm();
});

/**
 * Display file name when a file has been selected
 */

$(document).on("change", "[type='file']", function () {
    let text = "Choisir un fichier";
    if ($(this).prop('files').length > 0) {
        text = $(this).prop('files')[0].name;
    }

    $(this).next().text(text);
});

function enableSubmitBtn($btnSubmit) {
    $btnSubmit.prop("disabled", false);
    $btnSubmit.css("cursor", "pointer");
}

function disableSubmitBtn($btnSubmit) {
    $btnSubmit.prop("disabled", true);
    $btnSubmit.css("cursor", "default");
}

function isValidForm() {
// restore
    let $form = $("form");
    $form.find(".is-invalid").removeClass("is-invalid");
    $(".invalid-feedback").hide();
    $form.prop("novalidate", false);
    disableSubmitBtn($("[type='submit']"));

    $("form input, form textarea").each(function () {
        if ($(this).val()) {
            enableSubmitBtn($(this).parents("form").find("[type='submit']"));
        }
    });

// check size of file
    $("[type='file']").each(function () {
        let $parent = $(this).parents(".custom-file");
        let $form = $(this).parents("form");
        let $invalidFeedBack = $form.find(".invalid-feedback");

        if ($(this).prop('files').length > 0) {
            let file = $(this).prop('files')[0]; // puts all files into an array
            let fileSize = ((file.size / 1024) / 1024).toFixed(0); // MB

            // fileSize over 2MB
            if (fileSize > 2) {
                $parent.addClass("is-invalid");
                $invalidFeedBack.find(".size").text(fileSize + "Mo");
                $invalidFeedBack.show();
                $form.prop("novalidate", true);
                disableSubmitBtn($form.find("[type='submit']"));
            }
        }

    });
}
