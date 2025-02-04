function updateDateTime() {
    var dateTimeElement = document.getElementById("dateTime");
    var currentDate = new Date();
    var options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric', 
        hour: 'numeric', 
        minute: 'numeric', 
        second: 'numeric' 
    };
    var dateTimeString = currentDate.toLocaleDateString('fr-FR', options);
    dateTimeElement.textContent = dateTimeString;
}

// Mettre à jour la date et l'heure toutes les secondes
setInterval(updateDateTime, 1000);

// Appel initial pour afficher la date et l'heure
updateDateTime();
