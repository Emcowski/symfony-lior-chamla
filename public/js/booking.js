$(document).ready(function() {
    // Appliquer le datepicker bootstrap aux champs date d'arrivée et date de départ dans Réserver une annonce
    $('#booking_startDate, #booking_endDate').datepicker({
        format: 'dd/mm/yyyy',
        datesDisabled: [
            {% for day in day.notAvailableDays %}
                "{{ day.format('d/m/Y') }}",
            {% endfor %}
        ],
    });
});