/**
 * Pre-fill dateRangePicker if an event is being edited
 * if it's being created, pre-fill with "date_now"
 */
const $startDateInput = $("#start_date");
const $endDateInput = $("#end_date");

const now_date = new Date().toLocaleDateString();

const start_date = $startDateInput.val() === "" ? now_date : $startDateInput.val();
const end_date = $endDateInput.val() === "" ? now_date : $endDateInput.val();

$('input[name="range_date"]').daterangepicker({
    "timePicker24Hour": true,
    "timePicker": true,
    "locale": {
        "format": "DD/MM/YYYY HH:mm",
        "separator": " - ",
        "applyLabel": "Appliquer",
        "cancelLabel": "Annuler",
        "fromLabel": "De",
        "toLabel": "à",
        "customRangeLabel": "Personnaliser",
        "weekLabel": "S",
        "daysOfWeek": [
            "Dim",
            "Lun",
            "Mar",
            "Mer",
            "Jeu",
            "Ven",
            "Sam"
        ],
        "monthNames": [
            "Janvier",
            "Février",
            "Mars",
            "Avril",
            "Mai",
            "Juin",
            "Juillet",
            "Août",
            "Septembre",
            "Octobre",
            "Novembre",
            "Décembre"
        ],
        "firstDay": 1,
    },
    "startDate": start_date,
    "endDate": end_date
}, function(start, end, label) {
    $("#start_date").val(start.format('YYYY-MM-DD HH:mm'));
    $("#end_date").val(end.format('YYYY-MM-DD HH:mm'));

    console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
});