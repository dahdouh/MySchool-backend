$(".d-none input").prop("required", false);

/**
 * Add a question
 */
$(document).on("click", ".addQuestion", function () {
    let $form = $(this).parents("form");
    let $wrapperQuestion = $form.find(".blockQuestion.d-none").find(".wrapperQuestion, hr").clone();
    $form.find(".wrapperQuestion").last().after($wrapperQuestion);
    $wrapperQuestion.hide().slideDown();
    $wrapperQuestion.find("input").prop("required", true);
    $(".d-none input").prop("required", false);

    resetNumberQuestion();
});

/**
 * Delete a question
 */
$(document).on("click", ".deleteQuestion", function (e) {
    let $wrapperQuestion = $(this).parents(".wrapperQuestion");
    if ($wrapperQuestion.find("input").val() !== "") {
        let r = confirm("Êtes-vous sûr de vouloir supprimer cette question ?");
        if (!r) {
            return;
        }
    }
    $wrapperQuestion.prev("hr").remove();
    $wrapperQuestion.slideUp().remove();

    let questionId = $(this).attr("data-questionId");
    let quizId = $(this).attr("data-quizId");
    if (questionId) {
        let $hiddenInput = $("#" + quizId).find("[name='deletedQuestionsId']");
        let deletedQuestionsId = $hiddenInput.val() ? $hiddenInput.val().split(",") : [];
        deletedQuestionsId.push(questionId);
        $hiddenInput.val(deletedQuestionsId.join(","));
    }

    resetNumberQuestion();
});

/**
 * Add a choice
 */
$(document).on("click", ".addChoice", function () {
    let $wrapperQuestion = $(this).parents(".wrapperQuestion");
    let $blockChoice = $wrapperQuestion.find(".blockChoice.d-none li").clone();

    $wrapperQuestion.find("ul").append($blockChoice);
    $blockChoice.hide().slideDown();
    $blockChoice.find("input").val("").prop("required", true);
    $(".d-none input").prop("required", false);

    resetNumberChoice();
});

/**
 * Delete a choice
 */
$(document).on("click", ".deleteChoice", function () {
    let $blockChoice = $(this).parents(".blockChoice");
    if ($blockChoice.find("input").val() !== "") {
        let r = confirm("Êtes-vous sûr de vouloir supprimer ce choix de réponse ?");
        if (!r) {
            return;
        }
    }
    $blockChoice.slideUp().remove();

    let choiceId = $(this).attr("data-choiceId");
    let quizId = $(this).attr("data-quizId");
    if (choiceId) {
        let $hiddenInput = $("#" + quizId).find("[name='deletedChoicesId']");
        let deletedChoicesId = $hiddenInput.val() ? $hiddenInput.val().split(",") : [];
        deletedChoicesId.push(choiceId);
        $hiddenInput.val(deletedChoicesId.join(","));
    }

    resetNumberChoice();
});

function resetNumberQuestion() {
    $("form").each(function (i) {
        $(this).find(".wrapperQuestion").each(function (j) {
            $(this).find(".questionLabel .number, .addChoice .number").text(j);
            $(this).find("input[id^='question']").attr("id", "question" + i + j);
            $(this).find("label[for^='question']").attr("for", "question" + i + j);
            $(this).find("input[id^='rightAnswer']").attr("id", "rightAnswer" + i + j);
            $(this).find("label[for^='rightAnswer']").attr("for", "rightAnswer" + i + j);
            resetNumberChoice();
        });
    });
}

function resetNumberChoice() {
    $("form").each(function (i) {
        $(this).find(".wrapperQuestion").each(function (j) {
            $(this).find(".blockChoice").each(function (k) {
                $(this).find("input").attr("name", "choice[" + (j - 1) + "][]");
                $(this).find("input").attr("id", "choice" + i + j + k);
                $(this).find("label").attr("for", "choice" + i + j + k);
            });
        });
    });
}