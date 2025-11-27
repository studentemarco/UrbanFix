<?php
class UserData{
    public int $id;
    public array $preferiti;
    public array $libriLetti;
    public array $lists;

    private static string $filePath = "usersdata.json";
    private static string $readPath = "accesso/usersdata.json";

    public function __construct(int $id) {
        $this->id = $id;
        $this->preferiti = [];
        $this->libriLetti = [];
        $this->lists = [
            "Read List" => [] // una lista vuota iniziale
        ];

        //$this->salva();
    }

    public function salva(): void {
        $utenti = [];
        if (file_exists(self::$filePath)) {
            $contenuto = file_get_contents(self::$filePath);
            $utenti = json_decode($contenuto, true) ?? [];
        }

        // Aggiunge il nuovo utente (convertito in array)
        $utenti[] = [
            "id" => $this->id,
            "preferiti" => $this->preferiti,
            "libriLetti" => $this->libriLetti,
            "lists" => $this->lists
        ];

        // Scrive tutto l’elenco di utenti nel file
        file_put_contents(self::$filePath, json_encode($utenti, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public static function getData(int $id): ?UserData {

        $contenuto = file_get_contents(self::$readPath);
        $utenti = json_decode($contenuto, true) ?? [];

        foreach ($utenti as $u) {
            if ($u["id"] === $id) {
                // Ricrea l’oggetto Utente a partire dai dati salvati
                $utente = new self($u["id"]);
                $utente->preferiti = $u["preferiti"];
                $utente->libriLetti = $u["libriLetti"];
                $utente->lists = $u["lists"];
                error_log("Utente trovato");
                return $utente;
            }
        }
        error_log($contenuto);
        return null; // Nessun utente trovato
    }

    public static function updateUser(UserData $updatedUser): bool {
        if (!file_exists(self::$readPath)) return false;

        $contenuto = file_get_contents(self::$readPath);
        $utenti = json_decode($contenuto, true) ?? [];
        $updated = false;

        foreach ($utenti as &$u) {
            if ($u["id"] === $updatedUser->id) {
                // Sovrascrive tutti i dati dell'utente con quelli dell'oggetto
                $u["preferiti"] = $updatedUser->preferiti;
                $u["libriLetti"] = $updatedUser->libriLetti;
                $u["lists"] = $updatedUser->lists;
                $updated = true;
                break;
            }
        }

        if ($updated) {
            file_put_contents(self::$readPath, json_encode($utenti, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return $updated;
    }
}
?>