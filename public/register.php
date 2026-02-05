<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main>
    <section class="form-section">
        <div class="form-container">
            <h1>Rekisteröidy</h1>
            <form class="form">
                <label for="first_name">Etunimi</label>
                <input type="text" id="first_name" placeholder="Etunimesi" required>

                <label for="last_name">Sukunimi</label>
                <input type="text" id="last_name" placeholder="Sukunimesi" required>

                <label for="email">Sähköposti</label>
                <input type="email" id="email" placeholder="Sähköposti" required>

                <label for="password">Salasana</label>
                <input type="password" id="password" placeholder="Salasana" required>

                <label for="password_confirm">Vahvista salasana</label>
                <input type="password" id="password_confirm" placeholder="Vahvista salasana" required>

                <button type="submit" class="btn-submit">Rekisteröidy</button>
            </form>

            <p class="form-text">
                Onko sinulla jo tili? 
                <a href="login.php">Kirjaudu tästä</a>
            </p>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
