# 🐾 PetNotes
### PetNotes to kompleksowy system zarządzania zdrowiem i codzienną opieką twoich pupili.<br>Aplikacja pozwala właścicielom na monitorowanie wizyt u weterynarza, harmonogramów karmienia, zabiegów pielęgnacyjnych czy prowadzenie kalendarza wydarzeń.
----
## 📝 Kluczowe funkcje
🔐 **Autoryzacja i Bezpieczeństwo**
* ***Logowanie i Rejestracja:*** Zaawansowana walidacja po stronie klienta (JS) oraz serwera. Przyciski akcji są blokowane do momentu poprawnego wypełnienia formularzy.
* ***Walidacja Fetch API:*** Sprawdzanie dostępności adresu e-mail w bazie danych w czasie rzeczywistym.
* ***Wymagania Hasła:*** System wymusza silne hasła (min. 13 znaków, duża litera, cyfra i znak specjalny).
* ***Polityka Prywatności:*** Wymagana akceptacja regulaminu przy rejestracji.

📅 **Zarządzanie Czasem (Calendar)**
* ***Interaktywny Kalendarz:*** Wizualne oznaczanie dni z wydarzeniami (kropki).
* ***Dynamiczne Wydarzenia:*** Dodawanie i usuwanie eventów za pomocą Fetch API bez przeładowania strony.
* ***Widoki:*** Lista wszystkich nadchodzących wydarzeń oraz dedykowany panel "Today's Events".

🐶🐱 **Profil i Karta Zwierzaka**
* ***Zarządzanie Zwierzętami:*** Możliwość dodawania wielu profilów (wymagane dane: typ, imię, data urodzenia, płeć, rasa, kolor).
* ***Personalizacja:*** Przesyłanie zdjęć pupila oraz numeru mikrochipu (opcjonalne).
* ***Edycja i Zarządzanie:*** Pełna kontrola nad danymi oraz możliwość usunięcia profilu zwierzaka.

❤️‍🩹 **Zdrowie i Pielęgnacja (Health & Care)**
* ***Książeczka Zdrowia (HealthBook):*** Rejestr szczepień, odrobaczania, zabiegów i operacji oraz wizyt u weterynarza.
* ***Opieka (Care):*** Monitoring wagi (z podsumowaniem 4 ostatnich wpisów), groomingu, strzyżenia i przycinania pazurów.
* ***Logika "Last History":*** Automatyczne wyświetlanie daty ostatniego wpisu lub statusu "No history".

🍎 **Żywienie (Nutrition)**
* ***Moduły Żywieniowe:*** Zarządzanie wrażliwościami pokarmowymi, ulubionym jedzeniem i suplementami (limit do 20 wpisów na sekcję).
* ***Harmonogram Karmienia:*** Planowanie posiłków (limit do 8 wpisów).
* ***Inteligentne UI:*** Przyciski dodawania znikają automatycznie po osiągnięciu limitu wpisów.

📲 **Panel Administratora**
* ***Pełne zarządzanie użytkownikami:*** edycja danych, usuwanie oraz system blokowania (Ban/Unban).
* Zbanowani użytkownicy otrzymują ***natychmiastową blokadę*** dostępu do konta.
<br><br>
## ⚠️ Obsługa Błędów
* ***401 (Unauthorized):*** Wygaśnięcie sesji (timeout).
* ***403 (Forbidden):*** Próba nieautoryzowanego dostępu (np. user do panelu admina).
* ***404 (Not Found):*** Nieprawidłowy adres URL.
* ***422 (Unprocessable Entity):*** Błędy formatowania danych.
* ***500 (Internal Server Error):*** Wewnętrzne problemy z serwerem.
----
## 📝 Rejestracja
<br><br>
## 📝 Logowanie
<br><br>
## 📝 Strona Welcome
<br><br>
## 📝 Kalendarz
<br><br>
## 📝 Moje zwierzaki
<br><br>
## 📝 Features zwierzaka
<br><br>
## 📝 Edycja profilu zwierzaka
<br><br>
## 📝 Książeczka zdrowia
<br><br>
## 📝 Opieka nad zwierzakiem
<br><br>
## 📝 Żywienie
<br><br>
## 📝 Panel admina
