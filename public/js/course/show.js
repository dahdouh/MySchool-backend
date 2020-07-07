const $btnCorrection = $("button.correction");

/**
 * Show the correction for an exercise or an exam
 */

$btnCorrection.on("click", function () {
    $(this).parents(".card-body").find("div.correction").slideToggle();
    $(this).toggleClass("btn-outline-success btn-success")
});