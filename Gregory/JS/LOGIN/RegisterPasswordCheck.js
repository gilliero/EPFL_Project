function checkPasswords() {
    var password1 = document.getElementById("password").value;
    var password2 = document.getElementById("confirm-password").value;

    if (password1 !== password2) {
        alert("Les mots de passe ne correspondent pas.");
        return false; // Prevent form submission
    }

    return true; // Allow form submission
}
