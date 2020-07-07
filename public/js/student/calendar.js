let options = {
    language: 'fr-FR',
    events_source: $("#events_source").text(), // populate calendar with events
    view: $("#calendarSection .btn-group button.active").attr("data-calendar-view"),
    tmpl_path: $("#tmpl_path").text(),
    tmpl_cache: false,
    day: "now",
    modal: "#events-modal",
    modal_type : "template",
    modal_title : function (e) { return e.title },
    first_day: 1, // Start week with Monday
    onAfterViewLoad: function(view) {
        $('#calendarSection h3').text(this.getTitle());
        $('#calendarSection .btn-group button').removeClass('active');
        $('#calendarSection button[data-calendar-view="' + view + '"]').addClass('active');
    },
    classes: {
        months: {
            general: 'label'
        }
    }
};

let calendar = $('#calendar').calendar(options);

$('#calendarSection .btn-group button[data-calendar-nav]').each(function() {
    let $this = $(this);
    $this.click(function() {
        calendar.navigate($this.data('calendar-nav'));
    });
});

$('#calendarSection .btn-group button[data-calendar-view]').each(function() {
    let $this = $(this);
    $this.click(function() {
        calendar.view($this.data('calendar-view'));
    });
});

$('#events-modal .modal-header, #events-modal .modal-footer').click(function(e){
    // e.preventDefault();
    // e.stopPropagation();
});