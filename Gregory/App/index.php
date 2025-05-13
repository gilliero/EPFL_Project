<?php

session_start();
// Si l'utilisateur est déjà connecté, on le redirige
if (isset($_SESSION["user_id"])) {
    header("Location: ./connected.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>EPFL - Connexion</title>
    <link rel="stylesheet" href="./login.css">
</head>
<body>
    <main>
        <h1>Connexion</h1>
        <form action="./login.php" method="post">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <input type="submit" value="Se connecter">
        </form>
        <br>
        <br>
    </main>
</body>
</html>
