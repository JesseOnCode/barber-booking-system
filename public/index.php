<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main>

    <section id="hero" class="hero">
    <div class="hero-content">
        <h1>Perinteistä ja modernia parturointia Kuopion sydämessä</h1>
        <p>Laadukasta työnjälkeä, rento tunnelma ja henkilökohtaista palvelua – ajanvaraus helposti verkossa.</p>
        <a href="booking.php" class="btn-primary">Varaa aika</a>
    </div>
    </section>

   <section id="about">
    <div class="about-content">
        <h2>Meistä</h2>
        <p>Olemme Kuopiossa toimiva parturiliike, joka yhdistää perinteisen
           parturointitaidon ja modernit tyylit. Meille tärkeintä on
           asiakastyytyväisyys ja huolellinen työnjälki.</p>
        <p>Jokainen leikkaus tehdään yksilöllisesti asiakkaan toiveet ja
           hiustyyppi huomioiden. Tervetuloa viihtymään ja rentoutumaan
           ammattitaitoiseen käsittelyyn.</p>
    </div>
    </section>

    <section id="services">
    <div class="services-content">
        <h2>Palvelut ja hinnat</h2>
        <ul class="service-list">
            <li>
                <strong>Miesten hiustenleikkaus</strong>  25€
            </li>
            <li>
                <strong>Parranleikkaus</strong>  15€
            </li>
            <li>
                <strong>Hiusten ja parran yhdistelmä</strong>  35€
            </li>
            <li>
                <strong>Hiusten pesu ja föönaus</strong>  10€
            </li>
        </ul>
    </div>
    </section>

    <section id="booking-cta">
    <div class="container">
        <h2>Varaa aika helposti verkossa</h2>
        <p>Valitse sinulle sopiva aika ja tule kokemaan laadukas parturipalvelu.</p>
        <p><strong>Huom:</strong> Ajanvaraus edellyttää kirjautumista asiakastilille.</p>
        <?php if(isset($_SESSION['user_id'])): ?>
            <!-- Käyttäjä kirjautunut -->
            <a href="booking.php" class="btn-primary">Varaa aika nyt</a>
        <?php else: ?>
            <!-- Käyttäjä ei kirjautunut -->
            <a href="login.php" class="btn-primary">Varaa aika nyt</a>
        <?php endif; ?>
    </div>
    </section>

    <section id="contact">
    <div class="contact-content">
        <h2>Yhteystiedot</h2>
        <p>Palvelemme ajanvarauksella:</p>
        <p><strong>Osoite:</strong> Parturikuja 5, 70800 Kuopio</p>
        <p><strong>Sähköposti:</strong> info@barbershopkuopio.fi</p>
        <p><strong>Puhelin:</strong> 040 123 4567</p>
        <p><strong>Aukioloajat:</strong><br>
           Ma–Pe 9:00–18:00<br>
           La 10:00–15:00<br>
           Su Suljettu</p>
    </div>
    </section>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
?>
