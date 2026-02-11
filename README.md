# 💈 BarberShop - Ajanvarausjärjestelmä

Moderni parturi-varausjärjestelmä, joka tarjoaa käyttäjille helpon tavan varata aikoja verkossa. Projekti on osa ohjelmistokehittäjäopintojani ja portfolio-työtäni.

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=flat&logo=javascript&logoColor=black)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat&logo=css3&logoColor=white)

---

## ✨ Ominaisuudet

- 🔐 **Turvallinen käyttäjäautentikointi** - Rekisteröinti ja kirjautuminen
- 📅 **Dynaaminen ajanvarausjärjestelmä** - Valitse palvelu, päivä ja vapaa aika
- ⏰ **Reaaliaikainen aikojen tarkistus** - Näkee vain vapaat ajat
- 📱 **Täysin responsiivinen design** - Toimii mobiilissa, tabletissa ja tietokoneella
- 🔒 **Turvallisuus etusijalla:**
  - PDO prepared statements (SQL-injection esto)
  - Password hashing (bcrypt)
  - CSRF-suojaus kaikissa lomakkeissa
  - XSS-suojaus
  - Session-turvallisuus

---

## 🛠️ Teknologiat

### Backend
- **PHP 8.x** - Palvelinpuolen logiikka
- **MySQL** - Tietokanta
- **PDO** - Tietokanta-abstraktio ja turvallinen kyselyiden käsittely

### Frontend
- **HTML5** - Semanttinen rakenne
- **CSS3** - Moderni tyylittely (Flexbox, Grid, Custom Properties)
- **JavaScript (ES6+)** - Dynaaminen käyttöliittymä
- **Fetch API** - Asynkroniset HTTP-pyynnöt

---

## 🚀 Asennus

### Vaatimukset
- PHP >= 8.0
- MySQL >= 5.7
- Apache/Nginx web-palvelin (esim. XAMPP)
- Git (valinnainen)

### Asennusohjeet

1. **Kloonaa repositorio**
```bash
git clone https://github.com/JesseOnCode/barber-booking-system.git
cd barber-booking-system
```

2. **Konfiguroi ympäristömuuttujat**
```bash
cp .env.example .env
```
Avaa `.env` tiedosto ja täytä tietokanta-asetukset:
```env
DB_HOST=localhost
DB_NAME=barbershop
DB_USER=root
DB_PASS=your_password_here
```

3. **Luo tietokanta**

Vaihtoehto A - phpMyAdmin:
- Avaa `http://localhost/phpmyadmin`
- Klikkaa "SQL" -välilehti
- Kopioi `database/schema.sql` sisältö ja suorita

Vaihtoehto B - Komentorivi:
```bash
mysql -u root -p < database/schema.sql
```

4. **Konfiguroi web-palvelin**

XAMPP:ssa aseta document root osoittamaan `public/` kansioon tai käytä:
```
http://localhost/barber-booking-system/public/
```

5. **Valmista!**

Avaa selaimessa ja aloita käyttö.

---

## 📁 Projektin rakenne
```
barber-booking-system/
├── config/              # Konfiguraatiotiedostot
├── database/            # SQL-skriptit ja tietokantarakenne
│   └── schema.sql       # Tietokantarakenne
├── includes/            # PHP-komponentit ja apufunktiot
│   ├── config.php       # Tietokantayhteys ja asetukset
│   ├── csrf.php         # CSRF-suojaus
│   ├── header.php       # Sivun header
│   └── footer.php       # Sivun footer
├── public/              # Julkinen webroot
│   ├── assets/
│   │   ├── css/         # Tyylit
│   │   ├── js/          # JavaScript
│   │   └── images/      # Kuvat
│   ├── index.php        # Etusivu
│   ├── login.php        # Kirjautuminen
│   ├── register.php     # Rekisteröinti
│   ├── booking.php      # Ajanvaraus
│   └── get_available_times.php  # API vapaille ajoille
├── .env.example         # Ympäristömuuttujien malli
├── .gitignore          # Git ignore tiedosto
└── README.md           # Tämä tiedosto
```

---

## 🔒 Turvallisuus

Projektissa on implementoitu useita turvallisuusparhaita käytäntöjä:

### Toteutetut turvallisuusominaisuudet:
- ✅ **SQL Injection esto** - PDO prepared statements
- ✅ **XSS esto** - htmlspecialchars() kaikissa käyttäjäsyötteissä
- ✅ **CSRF-suojaus** - Tokenit kaikissa lomakkeissa
- ✅ **Salasanojen hashays** - password_hash() ja password_verify()
- ✅ **Session-turvallisuus** - HTTPOnly cookies, session regeneration
- ✅ **Input-validointi** - Sähköposti, salasanan pituus, päivämäärät
- ✅ **Ympäristömuuttujat** - Salasanat .env-tiedostossa (ei GitHubissa)

---

## 📸 Kuvakaappaukset

### Etusivu
Moderni ja selkeä landing page palveluinformaatiolla.

### Ajanvaraus
Dynaaminen varausjärjestelmä joka näyttää vain vapaat ajat valitulla päivällä.

### Kirjautuminen & Rekisteröinti
Turvallinen käyttäjähallinta CSRF-suojauksella.

---

## 💡 Oppimiskokemukset

Tämän projektin aikana opin:

- **PHP-kehityksen parhaat käytännöt** - MVC-tyyppinen rakenne, koodin organisointi
- **Tietoturva-asiat** - CSRF, XSS, SQL injection ja niiden estäminen
- **Tietokantasuunnittelu** - Normalisointi, viiteavaimet, indeksit
- **Responsiivinen design** - Mobile-first lähestymistapa
- **Version hallinta** - Git workflow, commitit, .gitignore
- **Ongelmanratkaisu** - Session-hallinta, aikavyöhykkeet, päällekkäisten varausten esto

---

## 🗺️ Tulevat ominaisuudet

Suunnitteilla olevat parannukset:

- [ ] Käyttäjäprofiilisivu
- [ ] Varaushistoria ja varausten hallinta
- [ ] Sähköposti-vahvistukset varauksista
- [ ] Admin-hallintapaneeli
- [ ] Palveluiden hallinta tietokannasta
- [ ] Kalenterinäkymä varauksille
- [ ] SMS-muistutukset (Twilio)
- [ ] Maksuintegraatio (Stripe/PayPal)
- [ ] Monikielisyys (suomi/englanti)

---

## 🧪 Testaus

Sovellus on testattu:
- ✅ Chrome, Firefox, Safari, Edge -selaimilla
- ✅ Mobiililaitteilla (iOS & Android)
- ✅ Eri näyttökokoilla (320px - 1920px)
- ✅ XAMPP ympäristössä (Windows)

---

## 📝 Lisenssi

Tämä projekti on tehty oppimis- ja portfolio-tarkoituksiin. Vapaa käyttöön ja muokkaukseen.

---

## 👤 Tekijä

**Jesse**

- GitHub: [@JesseOnCode](https://github.com/JesseOnCode)
- LinkedIn: [www.linkedin.com/in/jessehaapaniemi]
- Portfolio: [www.jessehaapaniemi.com]

---

## 🙏 Kiitokset

Kiitos kaikille jotka ovat antaneet palautetta ja vinkkejä projektin kehitykseen!

---

## 📞 Yhteystiedot

Jos sinulla on kysyttävää projektista tai haluat keskustella yhteistyöstä, ota yhteyttä GitHubin, LinkedInin tai portfolioni kautta!

---

⭐ **Jos pidät projektista, anna sille tähti GitHubissa!**