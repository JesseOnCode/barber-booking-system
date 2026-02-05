<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main>
    <section class="form-section">
        <div class="form-container">
            <h1>Kirjaudu sisään</h1>
            <form class="form">
                <label for="email">Sähköposti</label>
                <input type="email" id="email" placeholder="Sähköposti" required>

                <label for="password">Salasana</label>
                <input type="password" id="password" placeholder="Salasana" required>

                <button type="submit" class="btn-submit">Kirjaudu</button>
            </form>

            <p class="form-text">
                Eikö sinulla ole vielä tunnusta? 
                <a href="register.php">Rekisteröidy tästä</a>
            </p>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
