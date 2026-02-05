<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirjaudu sisään - BarberShop</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

<div class="login-container">
    <h1>Kirjaudu sisään</h1>
    <form class="login-form">
        <label for="email">Sähköposti</label>
        <input type="email" id="email" placeholder="Sähköposti" required>

        <label for="password">Salasana</label>
        <input type="password" id="password" placeholder="Salasana" required>

        <button type="submit" class="btn-login">Kirjaudu</button>
    </form>

    <p class="register-text">
        Eikö sinulla ole vielä tunnusta? 
        <a href="register.php">Rekisteröidy tästä</a>
    </p>
</div>

</body>
</html>
