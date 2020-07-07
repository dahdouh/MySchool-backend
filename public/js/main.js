$(function () {
    /**
     * Enable feather icons
     */
    feather.replace();

    /**
     * Enable tooltips
     */
    $('[data-toggle="tooltip"]').tooltip();

    /**
     * Toggle sidebar menu
     */
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });

    /**
     * Alert message when clicking on an element that has the attribute data-alert
     */
    $(document).on("click", "[data-alert]", function(e) {
        let message = $(this).attr("data-alert");
        confirm(message) || e.preventDefault()
    });

    /**
     * Show modal by default with a .show class
     */
    $('.modal.show').modal('show');


    // Registration's popup events

    const $registerModal = $("#registerModal");

    /**
     * Display registration form
     */
    $registerModal.find(".step-one button").on("click", function () {
        $registerModal.find(".step-one").hide();
        let role = $(this).attr("data-role");
        $registerModal.find(".step-two").show();
        $registerModal.find('input[name="role"]').val(role);
    });

    /**
     * Reset the content when the modal is closed
     */
    $registerModal.on('hidden.bs.modal', function () {
        $registerModal.find(".step-one").show();
        $registerModal.find(".step-two").hide();
    });

    $("#registerModal .modal-footer button, #logInModal .modal-footer button").on("click", function() {
        $(this).parents(".modal").modal('hide');
    });



    /**
     * Pre-fill dateRangePicker if an event is being edited
     * if it's being created, pre-fill with "date_now"
     */
    const $registerHiddenBirthDate = $("#registerHiddenBirthDate");

    const now_date = new Date().toLocaleDateString();

    const date_birth = $registerHiddenBirthDate.val() === "" ? now_date : $registerHiddenBirthDate.val();
    console.log(date_birth);

    /**
     * Datepicker for birthday input in register modal
     */
    $('#registerBirthDate').daterangepicker({
        "singleDatePicker": true,
        "autoApply": true,
        "showDropdowns": true,
        "minYear": 1901,
        "maxYear": parseInt(moment().format('YYYY'),10),
        "locale": {
            "format": "DD/MM/YYYY",
            "applyLabel": "Appliquer",
            "cancelLabel": "Annuler",
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
        "startDate": date_birth
    }, function(start, end, label) {
        $("#registerHiddenBirthDate").val(start.format('YYYY-MM-DD'));

        console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
    });
});
