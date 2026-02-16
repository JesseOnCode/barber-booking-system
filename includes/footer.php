<?php
/**
 * Sivuston footer-komponentti
 * 
 * Sisältää yhteystiedot ja mobiilinavigaation toggle-skriptin.
 * 
 * @package BarberShop
 * @author Jesse
 */
?>

<!-- Footer -->
<footer id="site-footer">
    <div class="footer-content">
        <p>&copy; <?= date('Y') ?> BarberShop - Kaikki oikeudet pidätetään</p>
        <p>Osoite: Parturikuja 5, 70100 Kuopio</p>
        <p>Sähköposti: infodemo@barbershop.fi | Puhelin: 040 123 4567</p>
    </div>
</footer>

<!-- Mobiilinavigaation toggle-skripti -->
<script>
/**
 * Mobiilinavigaation avaus/sulkeminen
 */
const toggle = document.querySelector('.nav-toggle');
const nav = document.querySelector('.main-nav');

if (toggle && nav) {
    toggle.addEventListener('click', () => {
        nav.classList.toggle('active');
    });
}
</script>

</body>
</html>     