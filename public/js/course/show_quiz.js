/**
 * Next question
 */
$(document).on("click", ".nextQuestion", function () {
    let $parentBlockQuestion = $(this).parents(".blockQuestion");
    let $nextBlockQuestion = $parentBlockQuestion.next();

    if (!isQuestionAnswered($parentBlockQuestion)) {
        return;
    }

    saveAnswerAction($parentBlockQuestion);

    // if there is a next question
    if ($nextBlockQuestion.is(".blockQuestion")) {
        $parentBlockQuestion.hide();
        $nextBlockQuestion.show();
    }
});

/**
 * When the quiz is finished, show the score
 */
$(document).on("click", ".showScore", function () {
    let $parentBlockQuestion = $(this).parents(".blockQuestion");
    let $blockScore = $parentBlockQuestion.next(".blockScore");

    if (!isQuestionAnswered($parentBlockQuestion)) {
        return;
    }

    saveAnswerAction($parentBlockQuestion);

    $parentBlockQuestion.hide();
    $blockScore.show();
});

/**
 * Confirm alert before exiting a quiz
 */
$(".modal.quiz").on('hide.bs.modal', function (e) {
    if ($(this).find(".blockScore").is(":hidden") && $(this).find(".blockQuestion input").is(":checked")) {
        let res = confirm("Êtes-vous sûr de vouloir quitter ce quiz ? Vous perdrez toute votre progression !");
        if (res) {
            // if an attempt has already been saved,
            // delete it because the quiz was exited before being completed
            let quizAttemptId = $(this).find("[name='quizAttemptId']").first().val();
            if (quizAttemptId !== "") {
                deleteQuizAttempt(quizAttemptId);
            }
        } else {
            e.preventDefault();
        }
    }
});

/**
 * Checks if the question has been answered
 *
 * @param $parentBlockQuestion block of the question that has been answered
 *
 * @return boolean
 */
function isQuestionAnswered($parentBlockQuestion) {
    if (!$parentBlockQuestion.find("input").is(":checked")) {
        $parentBlockQuestion.find(".alert-danger").show();
        return false;
    } else {
        $parentBlockQuestion.find(".alert-danger").hide();
        return true;
    }
}

/**
 * Ajax save answer action
 *
 * @param $parentBlockQuestion  block of the question that has been answered
 */
function saveAnswerAction($parentBlockQuestion) {
    let $modal = $parentBlockQuestion.parents(".modal");
    let quizId = $parentBlockQuestion.find("[name='quizId']").val();
    let questionId = $parentBlockQuestion.find("[name='questionId']").val();
    let choiceId = $parentBlockQuestion.find("[type='radio']:checked").val();
    let quizAttemptId = $parentBlockQuestion.find("[name='quizAttemptId']").val();

    let params = {
        "quizId": quizId,
        "questionId": questionId,
        "choiceId": choiceId
    };

    if (typeof quizAttemptId !== "undefined") {
        params["quizAttemptId"] = quizAttemptId;
    }

    $.get('/course/api/quiz/save?' + $.param(params), function(json_response) {
        $modal.find(".blockQuestion").each(function () {
            $(this).find("[name='quizAttemptId']").each(function () {
                $(this).val(json_response["quizAttemptId"]);
            });
        });

        let score = json_response["score"];
        $modal.find(".blockScore").find(".score").text(score);
        let nbRightAnswers = json_response["nbRightAnswers"];
        let textRightAnswers = nbRightAnswers + " question" + ((nbRightAnswers > 1) ? "s" : "");
        $modal.find(".blockScore").find(".nbRightAnswers").text(textRightAnswers);

        $modal.find(".blockScore").find(".alert").hide();
        if (score > 10/2) {
            $modal.find(".blockScore").find(".alert-success").show();
        } else {
            $modal.find(".blockScore").find(".alert-secondary").show();
        }
    });
}

/**
 * Ajax delete quiz attempt action
 *
 * @param quizAttemptId  quiz attempt id to delete
 */
function deleteQuizAttempt(quizAttemptId) {
    let params = {"quizAttemptId": quizAttemptId};
    $.get('/course/api/quizAttempt/delete?' + $.param(params));
}