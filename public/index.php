<?php
/**
 * Etusivu
 *
 * Sisältää hero-osion, tietoa yrityksestä, hinnaston,
 * ajanvarauskehotteen ja yhteystiedot.
 *
 * @package BarberShop
 * @author  Jesse Haapaniemi
 */

require_once __DIR__ . '/../includes/header.php';
?>

<main>

    <!-- Hero-osio: Pääkuva ja slogan -->
    <section id="hero" class="hero">
    <div class="hero-content">
        <h1>Tervetuloa BarberShopiin!</h1>
<p>Laadukasta työnjälkeä, rento tunnelma ja henkilökohtaista palvelua – ajanvaraus helposti verkossa.</p>
        <a href="booking.php" class="btn-primary">Varaa aika</a>
    </div>
    </section>

    <!-- Meistä-osio: Yrityksen esittely -->
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

    <!-- Hinnasto-osio: Palvelut ja hinnat -->
    <section id="services">
    <div class="services-content">
        <h2>Hinnasto</h2>
        <ul class="service-list">
            <li>Hiustenleikkaus - 25€</li>
            <li>Parranleikkaus - 15€</li>
            <li>Koneajo - 12€</li>
            <li>Hiustenleikkaus + Parranleikkaus - 35€</li>
        </ul>
    </div>
    </section>

    <!-- Ajanvaraus-osio: Kehotus varata aika -->
    <section id="booking-cta">
    <div class="container">
        <h2>Varaa aika helposti verkossa</h2>
        <p>Valitse sinulle sopiva aika ja tule kokemaan laadukas parturipalvelu.</p>
        <p><strong>Huom:</strong> Ajanvaraus edellyttää kirjautumista asiakastilille.</p>

        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Kirjautunut käyttäjä ohjataan suoraan varaussivulle -->
            <a href="booking.php" class="btn-primary">Varaa aika nyt</a>
        <?php else: ?>
            <!-- Kirjautumaton käyttäjä ohjataan kirjautumissivulle -->
            <a href="login.php" class="btn-primary">Varaa aika nyt</a>
        <?php endif; ?>
    </div>
    </section>

    <!-- Yhteystiedot-osio -->
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

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>