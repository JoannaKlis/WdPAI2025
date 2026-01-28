# 🐾 PetNotes
### PetNotes to kompleksowy system zarządzania zdrowiem i codzienną opieką twoich pupili.<br>Aplikacja pozwala właścicielom na monitorowanie wizyt u weterynarza, harmonogramów karmienia, zabiegów pielęgnacyjnych czy prowadzenie kalendarza wydarzeń.

---
# 📈 Architektura
Architektura projektu opiera się na wzorcu MVC (Model-View-Controller), wspieranym przez wzorce Repository oraz Singleton.
<p align="center">
  <img width="450" height="450" alt="architektura" src="https://github.com/user-attachments/assets/060f65cd-cdbb-4454-82da-646c2281f83e" />
</p>

* ***Models (Modele & Repozytoria):*** Klasy takie jak UserRepository.php izolują logikę zapytań SQL od reszty aplikacji. Dzięki temu kontrolery nie muszą wiedzieć, jak skonstruowane są tabele w bazie.
* ***Views (Widoki):*** Szablony HTML, znajdują się w katalogu public/views/.
* ***Controllers (Kontrolery):*** Klasy takie jak SecurityController czy AppController odpowiadają za obsługę logiki i przepływ danych. AppController stanowi klasę bazową, dostarczając wspólne metody dla autoryzacji i renderowania widoków.

---
# 📊 Baza danych PostgreSQL
<p align="center">
  <img width="1200" height="1200" alt="baza" src="https://github.com/user-attachments/assets/cf4cc70e-2369-44f1-a792-b3a9dfb657b2" />
</p>

## 🖧 Relacje i Akcje na Referencjach
* ***Relacja Jeden-do-Jednego (1:1):*** Tabela user_bans posiada kolumnę user_id, która jest zdefiniowana jako PRIMARY KEY oraz REFERENCES users(id), dany użytkownik może mieć tylko jeden wpis o banie.
* ***Relacja Jeden-do-Wielu (1:N):*** Jeden użytkownik (users) może posiadać wiele zwierząt (pets). Jedno zwierzę posiada wiele szczepień, wag.
* ***Relacja Wiele-do-Wielu (M:N):*** Tabela event_participants łączy global_events ze zwierzętami (pets), pozwalając wielu zwierzętom uczestniczyć w jednym wydarzeniu.
* ***ON DELETE CASCADE:*** Zastosowano tę akcję we wszystkich kluczach obcych. Oznacza to, że usunięcie użytkownika automatycznie usuwa jego zwierzęta, a usunięcie zwierzaka usuwa całą jego historię medyczną i żywieniową.

## 👁️ Widoki
* ***v_pet_events_calendar:*** Łączy wydarzenia z danymi właściciela i zwierzęcia przy użyciu wielokrotnego JOIN. Pozwala to aplikacji pobrać "tytuł wydarzenia" i "zdjęcie pupila" jednym zapytaniem.
* ***v_pet_medical_history:*** Wykorzystuje operator UNION ALL do stworzenia jednolitej osi czasu z czterech różnych tabel (szczepienia, zabiegi, odrobaczanie, wizyty).

## ⚙️ Funkcje i wyzwalacze
* ***calculate_pet_age:*** Funkcja dynamicznie oblicza wiek zwierzęcia na podstawie daty urodzenia, zwracając czytelny tekst (np. "2 years" lub "1 month").
* ***log_sensitive_user_changes:*** Funkcja wyzwalacza śledząca zmiany roli administratora lub usuwanie użytkowników.
* ***trigger_audit_user_changes:*** Automatycznie zapisuje wpis w audit_logs po każdej operacji UPDATE lub DELETE na tabeli użytkowników.

## 📑 Transakcje
* ***READ COMMITTED w Repository.php:*** addPetEvent w PetEventRepository.php wykonuje dwie operacje INSERT (do global_events i event_participants) wewnątrz jednego bloku transakcyjnego. Jeśli dodanie uczestnika zawiedzie, system wycofa (Rollback) utworzenie samego wydarzenia.

## 𝖩𝗈𝗂𝗇 ➥
```
SELECT p.user_id 
FROM event_participants ep
JOIN pets p ON ep.pet_id = p.id
WHERE ep.event_id = :eventId
LIMIT 1;
```

---
# 🔄 Fetch API
* ***Rejestracja i Logowanie (AuthFormHandler):*** Wyświetla komunikaty o błędach bez konieczności odświeżania.
```
const response = await fetch(this.endpoint, {
    method: 'POST',
    body: new FormData(this.form) // Automatyczne pakowanie danych formularza
});
const result = await response.json(); // Dekodowanie odpowiedzi z PHP

if (result.success) {
    window.location.href = result.redirect; // Przekierowanie po sukcesie
} else {
    this.displayError(result.message); // Wyświetlenie błędu bez przeładowania
}
```
* ***Dodawanie nowych wydarzeń (CalendarManager.js):*** W kalendarzu Fetch API jest wykorzystywane do przesyłania nowych wydarzeń do bazy danych. Po otrzymaniu sukcesu z serwera, JavaScript dynamicznie aktualizuje tablicę zdarzeń i interfejs (wstrzykuje kod HTML), co daje efekt natychmiastowego zapisu.
```
const response = await fetch('/addEvent', {
    method: 'POST',
    body: formData
});
const result = await response.json();

if (result.success) {
    const newEvent = { id: result.id, ... }; // Tworzenie obiektu z danych serwera
    this.serverEvents.push(newEvent); // Aktualizacja lokalnej tablicy
    this.renderCalendar(); // Ponowne renderowanie kalendarza z nową kropką
    this.injectEventIntoLists(newEvent); // Dodanie karty do listy "Upcoming events"
}
```
* ***Usuwanie wydarzeń (CalendarManager.js):*** Po potwierdzeniu usunięcia w bazie przez serwer, skrypt usuwa odpowiedni element z DOM (używając selektora ID), co sprawia, że karta zdarzenia znika na oczach użytkownika.
```
const response = await fetch('/deleteEvent', {
    method: 'POST',
    body: formData
});
const result = await response.json();

if (result.success) {
    // Usunięcie elementu z lokalnej tablicy i z widoku DOM
    this.serverEvents = this.serverEvents.filter(e => e.id != eventId);
    document.getElementById(`event-${eventId}`)?.remove(); 
    this.checkEmptyLists(); // Sprawdzenie, czy lista jest pusta, by pokazać komunikat
}
```

---
# ➜] Logowanie
* ***Blokada wielu prób logowania:***
  * Mechanizm blokady: Jeśli licznik nieudanych logowań osiągnie wartość MAX_LOGIN_ATTEMPTS (5), system ustawia czas blokady na 5 minut.
  * Weryfikacja: Przy każdym żądaniu sprawdzany jest czas pozostały do końca blokady.
```
if (isset($_SESSION['lockout_until']) && $_SESSION['lockout_until'] > time()) {
    $minutes = ceil(($_SESSION['lockout_until'] - time()) / 60);
    echo json_encode([
        'success' => false, 
        'message' => "Too many failed attempts. Try again in $minutes minute(s)."
    ]);
    exit();
}
```
* ***Pobranie Użytkownika i Weryfikacja Hasła:***
  * Pobranie danych: Metoda getUserByEmail wykonuje zapytanie SQL SELECT do tabeli users.
  * Weryfikacja kryptograficzna: Hasło przysłane przez użytkownika jest porównywane z zahashowaną wersją (BCRYPT) zapisaną w bazie za pomocą password_verify.
```
$user = $this->userRepository->getUserByEmail($email);

if (!$user || !password_verify($password, $user['password'])) {
    $this->registerFailedAttempt(); // Zwiększa licznik prób
    echo json_encode(['success' => false, 'message' => 'Incorrect email or password!']);
    exit();
}
```
* ***Sprawdzenie statusu blokady (Ban):***
  * Relacja 1:1: System sprawdza istnienie rekordu w tabeli user_bans.
  * Decyzja: Jeśli użytkownik jest zbanowany, proces logowania zostaje przerwany mimo poprawnego hasła.
```
if ($this->userRepository->isUserBanned($user['id'])) {
    echo json_encode(['success' => false, 'message' => 'Your account has been banned!']);
    exit();
}
```

---
# 🕒 Sesja uytkownika
* ***Inicjalizacja sesji (Routing.php):*** Sesja jest uruchamiana globalnie w punkcie wejścia aplikacji. Dzięki umieszczeniu session_start() w konstruktorze klasy Routing, mamy pewność, że dane sesyjne są dostępne dla każdego kontrolera.
```
private function __construct() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $this->registerRoutes();
}
```
* ***Tworzenie sesji podczas logowania (SecurityController.php):*** W momencie poprawnego uwierzytelnienia, kluczowe dane użytkownika są zapisywane w superglobalnej tablicy $_SESSION.
```
session_regenerate_id(true); // Generuje nowy identyfikator sesji

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['last_activity'] = time(); // Znacznik czasu dla timeoutu
```
* ***Weryfikacja i utrzymanie sesji (AppController.php):*** Każdy kontroler dziedziczy po AppController, który zawiera metodę checkAuthentication(). Jest ona wywoływana przed dostępem do chronionych zasobów (np. profilu czy kalendarza):
  * Zabezpieczenie Cache: Nagłówki blokują zapisywanie wrażliwych stron w pamięci przeglądarki.
  * Auto-Logout (Timeout): Jeśli użytkownik jest nieaktywny przez więcej niż 15 minut (900 sekund), sesja wygasa.
```
protected function checkAuthentication() {
        // Blokada cache przeglądarki
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");

        $timeout_duration = 900; // 15 minut

        // Sprawdzenie timeoutu (tylko jeśli sesja istnieje)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
            $this->logoutAndRedirect('401');
        }

        // Sprawdzenie czy użytkownik jest zalogowany
        if (!isset($_SESSION['user_id'])) {
            // Jeśli nie ma sesji -> idź do logowania
            header("Location: /login");
            exit();
        }

        // Odświeżenie czasu ostatniej aktywności
        $_SESSION['last_activity'] = time();
    }
```

---
# ⏻ Wylogowywanie
* ***Manualne wylogowanie (SecurityController):*** Kiedy użytkownik kliknie przycisk "Logout", wywoływana jest metoda logout(), która czyści tablicję sesji, usuwa ciasteczko sesyjne, niszczy sesję i przekierowuje na stronę logowania.
```
public function logout() {
    $_SESSION = array(); // Czyszczenie zmiennych

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, // Kasowanie ciasteczka
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy(); // Zniszczenie sesji na serwerze
    $this->redirect('login'); // Powrót do logowania
    exit();
}
```
* ***Automatyczne wylogowanie po czasie (Timeout):*** Jeśli użytkownik nie podejmie żadnej akcji przez 15 minut, system wyloguje go automatycznie przy próbie odświeżenia strony.
```
$timeout_duration = 900; // 15 minut

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    $this->logoutAndRedirect('401');
}
```

---
# 🎭 Role i uprawnienia użytkowników
* ***Rola User:*** Ma dostęp do funkcji związanych z własnymi zwierzętami, profilem oraz kalendarzem.
* ***Rola Admin:*** Posiada uprawnienia do panelu administracyjnego, zarządzania wszystkimi użytkownikami oraz ich blokowania.
```
// Blokada dla zwykłych użytkowników (tylko Admin ma wstęp)
protected function checkAdmin() {
    $this->checkAuthentication(); // Sprawdza czy w ogóle zalogowany
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        $this->redirect('403'); // Błąd Forbidden
    }
}

// Blokada dla Adminów (np. Admin nie zarządza własnymi zwierzętami w tym widoku)
protected function checkUser() {
    $this->checkAuthentication();
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        $this->redirect('403');
    }
}
```

---
# 🛠️ Zarządzanie użytkownikami
Administrator zarządza użytkownikami poprzez UserRepository, który dostarcza metody do modyfikacji danych oraz nakładania blokad.
* ***Pobieranie listy użytkowników:*** System wykonuje złączenie LEFT JOIN z tabelą banów, aby wyświetlić status każdego konta.
* ***Edycja przez Admina:*** Możliwość zmiany imienia, nazwiska, e-maila oraz roli innego użytkownika.
* ***System Banowania:*** Relacja 1:1 między tabelą users a user_bans pozwala na trwałe lub czasowe wykluczenie użytkownika.
```
public function banUser(int $userId, string $reason = 'No reason provided'): void {
    $stmt = $this->database->connect()->prepare('
        INSERT INTO user_bans (user_id, reason) 
        VALUES (:id, :reason)
        ON CONFLICT (user_id) DO NOTHING
    ');
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
    $stmt->execute();
}
```

---
# 🛡️ Bezpieczestwo
1. Ochrona przed SQL Injection
```
$query = $this->database->connect()->prepare("SELECT * FROM users WHERE email = :email;");
$query->bindParam(':email', $email);
$query->execute();
```
2. Nie zdradzam czy email istnieje
```
if (!$user || !password_verify($password, $user['password'])) {
    $this->registerFailedAttempt();
    echo json_encode(['success' => false, 'message' => 'Incorrect email or password!']);
    exit();
}
```
3. Walidacja formatu email po stronie serwera
```
$genericEmailError = 'Email address is incorrect!';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $this->userRepository->getUserByEmail($email)) {
    echo json_encode(['success' => false, 'message' => $genericEmailError]);
    exit();
}
```
4. UserRepository zarządzany jako singleton
```
class UserRepository extends Repository {
    private static $instance = null;

    private function __construct() { 
        parent::__construct(); 
    }

    public static function getInstance(): UserRepository {
        return self::$instance ??= new self();
    }
```
5. Metoda login/register przyjmuje dane tylko na POST, GET tylko renderuje widok
```
public function login() {
    if (!$this->isPost()) {
        return $this->render("auth/login"); // Żądanie GET: tylko wyświetlamy formularz
    }

    // Żądanie POST: przetwarzamy dane i logujemy użytkownika
    header('Content-Type: application/json');
    // ... dalsza logika bezpieczeństwa
}
```

6. Ograniczam długość wejścia (email, hasło, imię, ...)
```
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    firstname VARCHAR(150) NOT NULL,
    lastname VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(200) NOT NULL,
    ...
);
```
```
if ($this->petRepository->countUserPets($userId) >= 50) {
    header("Location: /pets?error=limit_reached");
    exit;
}
```
7. Hasła przechowywane jako hash
```
$this->userRepository->createUser(
    $firstname, 
    $lastname, 
    $email, 
    password_hash($password, PASSWORD_BCRYPT)
);
```
8. Hasła nigdy nie są logowane w logach/errorach
```
if (!$user || !password_verify($password, $user['password'])) {
    $this->registerFailedAttempt(); // Loguje tylko fakt próby, nie jej treść
    echo json_encode(['success' => false, 'message' => 'Incorrect email or password!']);
    exit();
}
```
9. Po poprawnym logowaniu regeneruję ID sesji
```
unset($_SESSION['login_attempts'], $_SESSION['lockout_until']);

session_regenerate_id(true); 

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = $user['role'];
```
10. Cookie sesyjne ma flagi HttpOnly i Secure
```
public function logout() {
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params(); // Pobranie parametrów, m.in. httponly
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"] // Użycie flagi HttpOnly i Secure
        );
    }

    session_destroy();
    $this->redirect('login');
}
```
11. Limit prób logowania / blokada czasowa / CAPTCHA po wielu nieudanych próbach
```
private const MAX_LOGIN_ATTEMPTS = 5;
private const LOCKOUT_TIME = 300; // 5 minut

if (isset($_SESSION['lockout_until']) && $_SESSION['lockout_until'] > time()) {
    $minutes = ceil(($_SESSION['lockout_until'] - time()) / 60);
    echo json_encode([
        'success' => false, 
        'message' => "Too many failed attempts. Try again in $minutes minute(s)."
    ]);
    exit();
}
```
12. Waliduję złośoność hasła
```
private function isValidPassword(string $password): bool {
    return strlen($password) >= 13 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[0-9]/', $password) && 
           preg_match('/[^A-Za-z0-9]/', $password);
}
```
13. Przy rejestracji sprawdzam, czy email jest już w bazie
```
$genericEmailError = 'Email address is incorrect!';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $this->userRepository->getUserByEmail($email)) {
    echo json_encode(['success' => false, 'message' => $genericEmailError]);
    exit();
}
```
14. Dane wyświetlane w widokach są escapowane
```
protected function render(?string $template = null, array $variables = []) {
    if ($template === null) return;
    $templatePath = 'public/views/' . $template . '.html';
    if (!empty($variables)) extract($variables); // Kontrolowane przekazanie zmiennych do widoku
    ob_start();
    include file_exists($templatePath) ? $templatePath : 'public/views/errors/404.html';
    echo ob_get_clean(); // Wyświetlenie bezpiecznie przetworzonego bufora
}
```
```
protected function validateAndSanitizeFloat(string $input): ?string {
    $cleanInput = str_replace(',', '.', $input);
    // Jeśli wejście nie jest numeryczne, zwraca null
    return (!is_numeric($cleanInput) || (float)$cleanInput <= 0) ? null : $cleanInput;
}
```
15. W produkcji nie pokazuję stack trace / surowych błędów użytkownika
```
protected function renderError(int $code, string $message = '') {
    http_response_code($code); // Ustawienie poprawnego kodu HTTP
    
    // Przekazanie tylko zdefiniowanego komunikatu, bez stack trace
    return $this->render("errors/{$code}", [
        'errorMessage' => $message,
        'backUrl' => $_SERVER['HTTP_REFERER'] ?? '/welcome'
    ]);
}
```
16. Zwracam sensowne kody HTTP
```
protected function getValidatedFloat(string $key, string $errorMessage = "Incorrect data format!"): string {
    $value = $this->validateAndSanitizeFloat($_POST[$key] ?? '');
    
    if ($value === null) {
        $this->renderError(422, $errorMessage); // Zwraca 422 przy błędzie walidacji
        exit;
    }
    return $value;
}
```
```
if (!$event || $this->petEventRepository->getEventOwnerId($eventId) !== $_SESSION['user_id']) {
    http_response_code(403); // Blokada dostępu do cudzych zdarzeń
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
```
17. Hasło nie jest przekazywane do widoków ani echo/var_dump
```
if ($password !== $confirmedPassword) {
    // Zwraca tylko informację o błędzie, nie echo haseł
    echo json_encode(['success' => false, 'message' => 'Passwords should be the same!']);
    exit();
}
```
18. Z bazy pobieram tylko minimalny zestaw danych o użytkowniku
```
// Sukces logowania - do sesji trafiają tylko niezbędne identyfikatory
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = $user['role'];
// Dane takie jak data rejestracji czy hash hasła są odrzucane po weryfikacji
```
19. Mam poprawne wylogowanie - niszczy sesję użytkownika
```
public function logout() {
    $_SESSION = array(); // Czyszczenie zmiennych

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, // Kasowanie ciasteczka
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy(); // Zniszczenie sesji na serwerze
    $this->redirect('login'); // Powrót do logowania
    exit();
}
```

---
## 🔐 Autoryzacja i Bezpieczeństwo
Proces weryfikacji danych w formularzach logowania i rejestracji odbywa się asynchronicznie w czasie rzeczywistym. Dzięki wykorzystaniu Fetch API, system komunikuje się z serwerem w tle, pozwalając na natychmiastowe wyświetlanie komunikatów o błędach bez konieczności przeładowywania strony.
### 🛠️ Logowanie
* ***Dynamiczna Aktywacja:*** Przycisk logowania pozostaje zablokowany, dopóki wszystkie pola formularza nie zostaną wypełnione.
<p align="center">
  <img width="450" height="300" alt="logowanie-zablokowany-przycisk" src="https://github.com/user-attachments/assets/5fbd9439-7098-4d72-aa91-c163d609f171" />
  <img width="450" height="300" alt="logowanie-odblokowany-przycisk" src="https://github.com/user-attachments/assets/45c33277-2a1f-4fbd-bcf7-8438f77f7d74" />
</p>

* ***Ochrona przed Brute-force:*** W przypadku błędnych danych logowania, system zwraca generyczny komunikat "Incorrect email or password!", nie zdradzając, czy problemem jest e-mail, czy hasło.
<p align="center">
 <img width="200" height="350" alt="logowanie-maila-nie-ma-w-bazie" src="https://github.com/user-attachments/assets/f4a3060a-b1df-435c-9a06-17ffb257849f" />
</p>

* ***Sprawdzanie Statusu:*** System weryfikuje, czy konto użytkownika nie zostało zablokowane przez administratora przed udzieleniem dostępu.
<p align="center">
  <img width="200" height="350" alt="zbanowane-konto" src="https://github.com/user-attachments/assets/ee0d882b-60a6-4f03-9d30-57435eb8359c" />
</p>

* ***Limit prób logowania*** Zaimplementowano mechanizm blokady po 5 nieudanych próbach na okres 5 minut.
<p align="center">
  <img width="200" height="350" alt="X zbyt-wiele-prób" src="https://github.com/user-attachments/assets/7ee63ef4-8871-4e86-96c0-379896bd282b" />
</p>

### 🛠️ Rejestracja
* ***Dynamiczna Aktywacja:*** Przycisk rejestracji pozostaje zablokowany, dopóki użytkownik nie uzupełni wszystkich wymaganych pól oraz nie zaznaczy checkboxa akceptującego Politykę Prywatności.
<p align="center">
  <img width="450" height="300" alt="rejestracja" src="https://github.com/user-attachments/assets/14c48403-59c6-4e6f-bade-7e5e5c5f282b" />
</p>

* ***Dyskretna Weryfikacja E-mail:*** System nie informuje wprost, czy dany adres e-mail istnieje już w bazie. W przypadku duplikatu lub błędnego formatu wyświetlany jest jednolity, generyczny komunikat "Email address is incorrect!".
<p align="center">
  <img width="200" height="350" alt="rejestracja-email-jest-w-bazie" src="https://github.com/user-attachments/assets/74e11143-4419-407f-9a42-e34f854fd04b" />
</p>

* ***Podgląd Hasła*** Użytkownik ma możliwość podejrzenia wpisanego hasła przed wysłaniem formularza, co minimalizuje ryzyko pomyłek przy tworzeniu silnych zabezpieczeń.
<p align="center">
  <img width="450" height="300" alt="rejestracja-widoczne-haslo" src="https://github.com/user-attachments/assets/b6d179e8-0f11-4929-bd5b-39a739b7eed8" />
</p>

* ***Restrykcje Danych Osobowych:*** Pola imię i nazwisko są filtrowane pod kątem znaków numerycznych. Dopuszczalne są jedynie litery (w tym polskie znaki diakrytyczne).
<p align="center">
  <img width="200" height="350" alt="rejestracja-cyfry-w-imieniu" src="https://github.com/user-attachments/assets/29dc7e38-721e-41d1-baaa-f0e7169628ee" />
</p>

* ***Walidacja Formatów:*** System sprawdza poprawność adresu e-mail (wymagany znak @ oraz domena).
<p align="center">
  <img width="200" height="350" alt="rejestracja-niepoprawny-email" src="https://github.com/user-attachments/assets/b40350f5-2e8a-4a88-bcb1-98771ecacd93" />
</p>

* ***Standardy Silnego Hasła:*** System akceptuje jedynie hasła spełniające standardy bezpieczeństwa:
   * minimum 13 znaków,
   * przynajmniej jedna wielka litera,
   * minimum jedna cyfra,
   * co najmniej jeden znak specjalny.
<p align="center">
  <img width="200" height="350" alt="rejestracja-za-krótkie-haslo" src="https://github.com/user-attachments/assets/2898c406-d279-401c-b38d-04925e375503" />
</p>

* ***Zgodność Haseł:*** Mechanizm sprawdza identyczność pola hasła i powtórzonego hasła przed wysłaniem formularza.
<p align="center">
  <img width="200" height="350" alt="rejestracja-to-samo-haslo" src="https://github.com/user-attachments/assets/00276764-3395-4c7a-930e-64397f0fc20e" />
</p>

* ***Zasady Prywatności:*** Rejestracja jest możliwa wyłącznie po obowiązkowym zaakceptowaniu Polityki Prywatności.
<p align="center">
  <img width="200" height="350" alt="polityka-prywatności" src="https://github.com/user-attachments/assets/fff2947b-d075-4de3-bb69-c66ae9fcb51a" />
</p>

* ***User Experience:*** Po pomyślnym utworzeniu konta, użytkownik jest automatycznie przekierowywany do ekranu logowania z powiadomieniem "Account has been successfully created".
<p align="center">
<img width="200" height="350" alt="pomyślna-rejestracja" src="https://github.com/user-attachments/assets/2fe5a726-72c8-454f-a748-24f1adb53349" />
</p>

---
## ✅ Strona przywitania
Po poprawnym zalogowaniu, system kieruje użytkownika do centralnego punktu aplikacji. Strona welcome umożliwia:
  * przejście do panelu My Pets,
  * przejście do edycji profilu użytkownika,
  * przejście do kalendarza wydarzeń,
<p align="center">
  <img width="450" height="300" alt="ekran-welcome" src="https://github.com/user-attachments/assets/921e479d-4e50-4fc2-9e83-3c4a14860fff" />
  <img width="200" height="350" alt="welcome-ze-zdjeciem" src="https://github.com/user-attachments/assets/a87bd855-d2ab-416c-bf86-4261b329ced1" />
</p>

---
## 👤 Edycja profilu użytkownika
* ***Zakres edycji:*** Użytkownik ma możliwość aktualizacji swojego imienia, nazwiska oraz zmianę domyślnego zdjęcia profilowego.
<p align="center">
  <img width="450" height="300" alt="ekran-profile-z-zablokowanym-przyciskiem" src="https://github.com/user-attachments/assets/d0b76085-e39b-4078-8ed6-618d73d3fb60" />
</p>

* ***Zabezpieczenie przed nadmiarowymi żądaniami:*** Przycisk Save Changes pozostaje zablokowany, dopóki użytkownik nie wprowadzi rzeczywistej zmiany w stosunku do danych obecnie zapisanych w bazie.
<p align="center">
  <img width="200" height="350" alt="profile-zmienione-dane" src="https://github.com/user-attachments/assets/dad423a5-0360-4962-a052-5b9da585f95e" />
</p>

---
## 📅 Kalendarz wydarzeń
* ***Wizualna sygnalizacja:*** Dni, w których zaplanowano aktywności, są oznaczone w widoku miesięcznym dyskretnymi kropeczkami, co pozwala na szybki przegląd zajętości miesiąca.
<p align="center">
  <img width="200" height="350" alt="calendar-mobile" src="https://github.com/user-attachments/assets/2be4c8d7-275d-41cc-b079-c91011d6b19f" />
</p>

* ***Dynamiczne Wydarzenia:*** Kliknięcie w konkretny dzień automatycznie otwiera formularz dodawania nowego wydarzenia z już przypisaną datą. Nowy event można również dodać w dowolnym momencie za pomocą dedykowanego przycisku z ikoną plusa.
<p align="center">
  <img width="450" height="300" alt="dodawanie-eventu" src="https://github.com/user-attachments/assets/c4246592-4e25-46ad-998d-29ebbf935ce9" />
</p>

* ***Personalizacja widoku:*** Użytkownik może łatwo przełączać się między klasycznym widokiem kalendarza a pełną listą wszystkich nadchodzących wydarzeń.
* ***Sekcja "Today's Events"*** Obok głównego kalendarza znajduje się dedykowany panel wyświetlający wyłącznie wydarzenia zaplanowane na bieżący dzień, co ułatwia codzienną organizację.
<p align="center">
  <img width="450" height="300" alt="same-wydarzenia" src="https://github.com/user-attachments/assets/0fe9ea23-5bfc-477b-a2f8-90ad9d05ea9c" />
</p>

* ***Zarządzanie zdarzeniami:***: Każde wydarzenie może zostać w prosty sposób usunięte z poziomu listy, co pozwala na bieżącą aktualizację planów.
<p align="center">
  <img width="200" height="350" alt="usuwanie-eventu" src="https://github.com/user-attachments/assets/51de51b7-ccc4-46e7-ae9b-69efca2d02bf" />
</p>

---
## 🐶🐱 My Pets i Karta Zwierzaka
* ***Zarządzanie kolekcją:*** Użytkownik ma natychmiastowy dostęp do listy wszystkich swoich zwierząt, z możliwością przejścia do ich indywidualnych kart szczegółowych.
<p align="center">
  <img width="200" height="350" alt="pets-mobilna" src="https://github.com/user-attachments/assets/4bae36b1-b321-4a96-b70d-54338df95c1c" />
</p>

* ***Intuicyjne dodawanie pupila:***
  * Wymagane dane: Podczas rejestracji nowego zwierzęcia system wymaga podania kluczowych informacji: typu, imienia, daty urodzenia, płci, rasy oraz umaszczenia.
  <p align="center">
    <img width="200" height="350" alt="dodawanie-nowego-zwierzaka" src="https://github.com/user-attachments/assets/72a598d7-2adf-4fc9-9bd9-cb89c64a6386" />
  </p>

  * Dane opcjonalne: Formularz pozwala na dobrowolne uzupełnienie numeru mikroczipu oraz dodanie zdjęcia pupila.
  <p align="center">
    <img width="200" height="350" alt="dodawanie-nowego-zwierzaka-pusty-microchip" src="https://github.com/user-attachments/assets/1e083eac-560a-4472-a030-12ced48e72e9" />
  </p>

  * Inteligentne przypisywanie mediów: Jeśli użytkownik nie wgra własnej fotografii, system automatycznie przypisze zwierzęciu grafikę domyślną.
  <p align="center">
    <img width="450" height="300" alt="nowy-zwierzak" src="https://github.com/user-attachments/assets/9caa595d-7239-49ac-b965-5e345cff24d9" />
  </p>
    
* ***Karta Zwierzaka (Features):*** Po wybraniu konkretnego profilu zwierzaka, użytkownik uzyskuje dostęp do panelu sterowania, z którego możliwe jest przejście do czterech wyspecjalizowanych modułów:
  * edycji profilu zwierzaka,
  * książeczki zdrowia,
  * opieki i pielęgnacji,
  * żywienia.
<p align="center">
  <img width="450" height="300" alt="features-web" src="https://github.com/user-attachments/assets/e1eb7cf4-7231-4df6-a005-61ca748beee9" />
  <img width="200" height="350" alt="features-mobile" src="https://github.com/user-attachments/assets/61bf78f3-8cfb-49d5-be9b-927a7752195b" />
</p>

---
## ✏️ Edycja profilu zwierzaka
* ***Wszechstronna modyfikacja:*** Użytkownik ma możliwość edycji wszystkich pól zdefiniowanych podczas dodawania zwierzaka, w tym zmiany imienia, rasy, numeru mikroczipu czy aktualizacji zdjęcia profilowego.
<p align="center">
  <img width="450" height="300" alt="dodanie-zdjecia-zwierzaka-webowe" src="https://github.com/user-attachments/assets/8820f335-fe8c-4bd2-9c5b-68eb5ec4708e" />
</p>

*  ***Inteligentny przycisk zapisu:*** Podobnie jak w przypadku profilu użytkownika, system stosuje logikę Smart Save. Przycisk Save Changes jest blokowany, jeśli dane w formularzu nie różnią się od tych przechowywanych w bazie danych, co eliminuje zbędne operacje zapisu.
<p align="center">
  <img width="200" height="350" alt="editpet-zblokowany-przycisk-zwierzaka-mobile" src="https://github.com/user-attachments/assets/b4ad670b-0a25-4b1d-a2c8-99032a164654" />
</p>

*  ***Zarządzanie obecnością (Usuwanie):*** Z poziomu tego widoku użytkownik ma również możliwość trwałego usunięcia profilu zwierzaka z systemu. Akcja ta jest nieodwracalna i powoduje usunięcie wszystkich powiązanych z danym zwierzęciem danych (zdrowie, żywienie, wydarzenia).
<p align="center">
  <img width="450" height="300" alt="okienko-potweirdzenia-usuniecia" src="https://github.com/user-attachments/assets/77907069-7a5a-41df-872e-bfc6b7a7d5d6" />
</p>

---
## ❤️‍🩹 Książeczka zdrowia
* ***Zarządzanie kategorią:*** Użytkownik ma dostęp do czterech sekcji:
  * szczepienia,
  * zabiegi i operacje,
  * odrobaczanie
  * oraz wizyty u weterynarza.
* ***Podgląd historii:*** Pod nazwą każdej kategorii wyświetlana jest data ostatniego wpisu. W przypadku braku danych system informuje użytkownika komunikatem "No history".
<p align="center">
  <img width="200" height="350" alt="healthBook-mobile" src="https://github.com/user-attachments/assets/2464cdea-0b07-4e1b-97b7-62a5e5eb4add" />
</p>

* ***Szczepienia i Odrobaczanie:*** Umożliwia dodawanie i usuwanie wpisów szczepień i odrobaczeń
    * Walidacja danych: System automatycznie konwertuje przecinki na kropki w polach liczbowych, zapobiegając błędom formatowania.
    * Wygoda wprowadzania: Wybór jednostek z listy rozwijanej oraz daty z interaktywnego kalendarza.
<p align="center">
  <img width="200" height="350" alt="vaccinations-mobile" src="https://github.com/user-attachments/assets/e73ec8b9-5c99-4e70-8791-73fec0c4e6d9" />
</p>

* ***Wizyty, Zabiegi i Operacje:*** Pozwalają na precyzyjne określenie terminu poprzez wybór daty z kalendarza oraz konkretnej godziny z listy rozwijanej.
<p align="center">
  <img width="450" height="300" alt="dodawanie-items" src="https://github.com/user-attachments/assets/3a7521c4-f9ed-4bfd-a2bb-a6a754681ec1" />
</p>

---
## ✂️ Opieka i Pielęgnacja
* ***Zarządzanie kategorią:*** Analogicznie do książeczki zdrowia, sekcja podzielona jest na kafle::
  * waga,
  * mycie,
  * strzyżenie
  * oraz przycinanie pazurów.
* ***Monitorowanie wagi:*** Panel obok wyżej wymienionych 4 kafli wyświetla 4 najnowsze pomiary wagi zwierzaka.
<p align="center">
  <img width="200" height="350" alt="care-mobile" src="https://github.com/user-attachments/assets/ad708b84-b1ea-4434-92f4-6fa5edc6e611" />
</p>

---
## 🍎 Żywienie (Nutrition)
* ***Moduły Żywieniowe:*** Zarządzanie wrażliwościami pokarmowymi, ulubionym jedzeniem i suplementami (limit do 20 wpisów na sekcję).
* ***Harmonogram Karmienia:*** Planowanie posiłków (limit do 8 wpisów).
* ***Inteligentne UI:*** Przyciski dodawania znikają automatycznie po osiągnięciu limitu wpisów.
<p align="center">
  <img width="200" height="350" alt="nutrition-mobile" src="https://github.com/user-attachments/assets/157cbae5-e673-4646-aea7-f32e9d6e711c" />
</p>

---
## 📲 Panel Administratora
* ***Pełne zarządzanie użytkownikami:*** edycja danych, usuwanie oraz system blokowania (Ban/Unban).
* Zbanowani użytkownicy otrzymują ***natychmiastową blokadę*** dostępu do konta.
<p align="center">
  <img width="450" height="300" alt="panel-admina" src="https://github.com/user-attachments/assets/c9e5daae-2bbd-4a40-9e02-f908349b7509" />
  <img width="200" height="350" alt="panel-admina-mobile" src="https://github.com/user-attachments/assets/1940ed32-638e-4b29-ae17-2f890b71274f" />
</p>

---
## ⚠️ Obsługa Błędów
* ***401 (Unauthorized):*** Wygaśnięcie sesji (timeout).
<p align="center">
  <img width="450" height="300" alt="401" src="https://github.com/user-attachments/assets/fcf8d34a-766a-4685-999d-eb4a06776e8a" />
</p>

* ***403 (Forbidden):*** Próba nieautoryzowanego dostępu (np. user do panelu admina).
<p align="center">
  <img width="450" height="300" alt="403" src="https://github.com/user-attachments/assets/b21de384-6944-4ea4-a9f5-89be8d169fa2" />
</p>

* ***404 (Not Found):*** Nieprawidłowy adres URL.
<p align="center">
  <img width="450" height="300" alt="404" src="https://github.com/user-attachments/assets/d9c8c2ad-bd6b-4ada-89f4-46b4758982cd" />
</p>

* ***422 (Unprocessable Entity):*** Błędy formatowania danych.
<p align="center">
  <img width="450" height="300" alt="422" src="https://github.com/user-attachments/assets/bbebd19e-c578-48f6-9861-0dae51a868ef" />
</p>

* ***500 (Internal Server Error):*** Wewnętrzne problemy z serwerem.
<p align="center">
  <img width="450" height="300" alt="500" src="https://github.com/user-attachments/assets/032d2dcd-bc5c-4177-b10b-8b45d433cc34" />
</p>

---
# 🔜 Plany na przyszłość
