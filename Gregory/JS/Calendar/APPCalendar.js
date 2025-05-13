document.addEventListener('DOMContentLoaded', function () {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const monthYear = document.getElementById('monthYear');
    const daysContainer = document.querySelector('.days');

    let currentDate = new Date();
    let currentMonth = currentDate.getMonth();
    let currentYear = currentDate.getFullYear();

    function generateCalendar(year, month) {
        GoodDay();
        // SuspectDay();
        const firstDay = new Date(year, month, 1);
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const startingDayOfWeek = (firstDay.getDay() + 6) % 7;

        monthYear.textContent = new Intl.DateTimeFormat('fr-FR', { year: 'numeric', month: 'long' }).format(firstDay);

        daysContainer.innerHTML = '';

        function openDayDetails(dayNumber, month, year) {
            const redirectURL = `../Heure/APPHeure.php?day=${dayNumber}&month=${month + 1}&year=${year}`;
            window.location.href = redirectURL;
        }

        // Affichez les jours du mois précédent
        for (let i = startingDayOfWeek - 1; i >= 0; i--) {
            const prevMonthDay = document.createElement('div');
            prevMonthDay.classList.add('inactive');
            prevMonthDay.textContent = new Date(year, month, -i).getDate();
            daysContainer.appendChild(prevMonthDay);
        }

        // Affichez les jours du mois en cours
        for (let i = 0; i < daysInMonth; i++) {
            const dayElement = document.createElement('div');
            const dayNumber = i + 1;
            dayElement.textContent = dayNumber;
            dayElement.classList.add('day');

            // Ajoutez la condition pour exclure tous les samedis et dimanches
            if ((new Date(currentYear, currentMonth, dayNumber).getDay() === 0 || new Date(currentYear, currentMonth, dayNumber).getDay() === 6)) {
                dayElement.classList.add('inactive-weekend');
            } else {
                dayElement.addEventListener('click', function () {
                    openDayDetails(dayNumber, currentMonth, currentYear);
                });
            }

            daysContainer.appendChild(dayElement);
        }
        // Affichez les jours du mois suivant
        const daysAfter = 42 - startingDayOfWeek - daysInMonth;
        for (let i = 0; i < daysAfter; i++) {
            const nextMonthDay = document.createElement('div');
            nextMonthDay.classList.add('inactive');
            nextMonthDay.textContent = new Date(year, month + 1, i + 1).getDate();
            daysContainer.appendChild(nextMonthDay);
        }
    }

    generateCalendar(currentYear, currentMonth);

    prevBtn.addEventListener('click', function () {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        generateCalendar(currentYear, currentMonth);
    });

    nextBtn.addEventListener('click', function () {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        generateCalendar(currentYear, currentMonth);
    });

    // Envoyé le mois avec la méthode GET et récupérer les valeurs de getDate.php et ajouter une classe au Bon jour
    function GoodDay() {
        currentMonthCorrected = parseInt(currentMonth) + 1;
        fetch("../../PHP/Calendar/getDate.php?month=" + currentMonthCorrected + "&year=" + currentYear)
            .then((response) => {
                return response.json();
            }).then((data) => {
                console.log(data);

                // Bon jour
                const GoodDate = data.filter((entry) => {
                    const parsedTime = parseTime(entry.heures_travaillees);
                    return entry.nombre_timbrages >= 4;
                }).map((entry) => {
                    return entry.date_timbrage;
                });

                console.log(GoodDate);
                const allDays = daysContainer.querySelectorAll('.day');
                allDays.forEach(dayElement => {
                    const dayNumber = parseInt(dayElement.textContent);
                    const currentDate = new Date(currentYear, currentMonth, dayNumber + 1).toISOString().slice(0, 10);
                    if (GoodDate.includes(currentDate)) {
                        dayElement.classList.add('good');
                    }
                });

                // Jour suspect
                const SuspectDay = data.filter((entry2) => {
                    const parsedTime2 = parseTime(entry2.heures_travaillees);
                    return entry2.nombre_timbrages > 0 && entry2.nombre_timbrages < 4;
                }).map((entry2) => {
                    return entry2.date_timbrage;
                });

                console.log(SuspectDay);
                allDays.forEach(dayElement => {
                    const dayNumber = parseInt(dayElement.textContent);
                    const currentDate = new Date(currentYear, currentMonth, dayNumber + 1).toISOString().slice(0, 10);
                    if (SuspectDay.includes(currentDate)) {
                        dayElement.classList.add('suspect');
                    }
                });

                // Bad jour
                allDays.forEach(dayElement => {
                    const dayNumber = parseInt(dayElement.textContent);
                    const currentDate = new Date(currentYear, currentMonth, dayNumber);
                    if (!data.some(entry => entry.date_timbrage === currentDate.toISOString().slice(0, 10)) && currentDate < new Date()) {
                        dayElement.classList.add('bad');
                    }
                });

            });
    }
});

// Fonction pour convertir une chaîne de caractères de temps en secondes
function parseTime(timeString) {
    const [hours, minutes, seconds] = timeString.split(":").map(Number);
    return hours * 3600 + minutes * 60 + seconds;
};
