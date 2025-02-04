document.addEventListener("DOMContentLoaded", function() {
  var seconds = 0;
  var minutes = 0;
  var hours = 0;
  var isPaused = false;
  var display = document.getElementById("chronoInput");
  var startPauseButton = document.getElementById("startPauseButton");

  function updateChrono() {
      if (!isPaused) {
          seconds++;
          if (seconds == 60) {
              seconds = 0;
              minutes++;
              if (minutes == 60) {
                  minutes = 0;
                  hours++;
              }
          }
          display.textContent = hours + ":" + (minutes < 10 ? "0" : "") + minutes + ":" + (seconds < 10 ? "0" : "") + seconds;
      }
  }

  // Démarre le chronomètre toutes les secondes
  var chronoInterval = setInterval(updateChrono, 1000);

  // Bouton Start/Pause
  startPauseButton.addEventListener("click", function() {
      isPaused = !isPaused;
      startPauseButton.textContent = isPaused ? "Start" : "Pause";
  });

  // Vous pouvez également ajouter un bouton de réinitialisation du chronomètre si nécessaire.
});