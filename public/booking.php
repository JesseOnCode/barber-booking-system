<?php include '../includes/header.php'; ?>

<main>

    <h1>Ajanvaraus</h1>

    <!-- VAIHEPOLKU -->
    <section id="booking-steps">
        <ol>
            <li>Valitse palvelu</li>
            <li>Valitse päivä</li>
            <li>Valitse aika</li>
            <li>Kirjaudu</li>
            <li>Vahvista</li>
        </ol>
    </section>

    <!-- PALVELUN VALINTA -->
    <section id="service-selection">
        <h2>1. Valitse palvelu</h2>

        <form>
            <label>
                <input type="radio" name="service">
                Hiustenleikkaus
            </label><br>

            <label>
                <input type="radio" name="service">
                Parta
            </label><br>

            <label>
                <input type="radio" name="service">
                Hiustenleikkaus + parta
            </label>
        </form>
    </section>

    <!-- PÄIVÄN VALINTA -->
    <section id="date-selection">
        <h2>2. Valitse päivä</h2>

        <input type="date">
    </section>

    <!-- AJAN VALINTA -->
    <section id="time-selection">
        <h2>3. Valitse aika</h2>

        <p>
            Saatavilla olevat ajat näytetään valitun päivän mukaan.
        </p>

        <ul>
            <li>10:00</li>
            <li>11:00</li>
            <li>12:00</li>
            <li>13:00</li>
        </ul>
    </section>

    <!-- VAHVISTUS -->
    <section id="booking-confirm">
        <h2>Vahvistus</h2>

        <p>
            Kirjautuminen vaaditaan ennen varauksen vahvistamista.
        </p>

        <a href="login.php">Kirjaudu / Rekisteröidy</a>
    </section>

</main>

<?php include '../includes/footer.php'; ?>