<?php
/**
 * Sivuston footer-komponentti
 *
 * Sisältää:
 * - Yhteystiedot (osoite, sähköposti, puhelin)
 * - Copyright-tiedot
 * - JavaScript-tiedostojen latauksen
 * - HTML-dokumentin sulkemiset (body, html)
 *
 * @package BarberShop
 * @author  Jesse Haapaniemi
 */
?>

<!-- Footer-osio -->
<footer id="site-footer">
    <div class="footer-content">
        <!-- Copyright -->
        <p>&copy; <?= date('Y') ?> BarberShop - Kaikki oikeudet pidätetään</p>
        
        <!-- Yhteystiedot -->
        <p>Osoite: Parturikuja 5, 70100 Kuopio</p>
        <p>Sähköposti: infodemo@barbershop.fi | Puhelin: 040 123 4567</p>
    </div>
</footer>

<!-- JavaScript: Mobiilinavigaation toiminnallisuus -->
<script src="/barber-booking-system/public/assets/js/main.js"></script>
</body>
</html>