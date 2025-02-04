<?php
$to = "jeremy.noth@epfl.ch";
$subject = "Test d'envoi d'e-mail depuis MAMP";
$message = "Ceci est un test d'envoi d'e-mail depuis MAMP.";
$headers = "From: jeremy.noth@netplus.ch";

if (mail($to, $subject, $message, $headers)) {
    echo "E-mail envoyé avec succès !";
} else {
    echo "Échec de l'envoi de l'e-mail.";
}
?>
