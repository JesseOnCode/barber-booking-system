<?php include '../includes/header.php'; ?>

<section id="booking">
    <div class="booking-content">
        <h2>Varaa aika</h2>
        <form class="booking-form" action="#" method="POST">
            <label for="service">Valitse palvelu</label>
            <select id="service" name="service" required>
                <option value="">-- Valitse palvelu --</option>
                <option value="haircut">Hiustenleikkaus - 25€</option>
                <option value="beard">Parranleikkaus - 15€</option>
                <option value="shave">Koneajo - 20€</option>
                <option value="combo">Hiustenleikkaus + Parranleikkaus - 35€</option>
            </select>

            <label for="date">Päivämäärä</label>
            <input type="date" id="date" name="date" required>

            <label for="time">Aika</label>
            <input type="time" id="time" name="time" required>

            <label for="notes">Lisätiedot</label>
            <textarea id="notes" name="notes" rows="4" placeholder="Kirjoita lisätietoja..."></textarea>

            <button type="submit" class="btn-primary">Varaa aika</button>
        </form>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
