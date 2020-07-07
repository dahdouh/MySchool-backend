const $subject = $("#subject");
const $duration = $("#duration");
const $subscriptionPrice = $("#subscriptionPrice");

const subjectPrice = $("#subjectPrice").text() * 1;
const monthlyRate = $("#monthlyRate").text() * 1;

$("select").on("change", function () {
    let numberSubjects = $subject.val().length * 1;
    let duration = $duration.val() * 1;

    let price = calculateSubscriptionPrice(numberSubjects, duration);

    $subscriptionPrice.text(price);
});

function calculateSubscriptionPrice(numberSubjects, duration) {
    let price = (numberSubjects * subjectPrice) * Math.pow(monthlyRate, duration) * duration;
    return price.toLocaleString(undefined, { minimumFractionDigits: 2 });
}