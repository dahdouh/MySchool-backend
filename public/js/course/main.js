const $subjectSelectBlock = $("#subjectSelect");
const $subjectSelect = $("select#subject");
const $levelSelect = $("select#level");

/**
 * Load subjects available to the level selected
 */
$levelSelect.on("change", function () {

    if ($subjectSelectBlock.is(":hidden")) {
        $subjectSelectBlock.removeClass("d-none").hide().fadeIn();
    }

    $.get('/course/api/subjects?id_level=' + $(this).val(), function(json_response) {

        $subjectSelect.empty();
        $subjectSelect.selectpicker("refresh");

        json_response['subjects'].forEach(function(subject) {
            $subjectSelect.append($('<option>', {
                value: subject.id,
                text: subject.name
            }));
        });

        $subjectSelect.selectpicker("refresh");
    });

});