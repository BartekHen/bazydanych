<?php

class AdminModel
{
    public static function allClasses(PDO $pdo): array
    {
        return $pdo->query('SELECT * FROM klasa ORDER BY stopien, nazwa')->fetchAll();
    }

    public static function allSubjects(PDO $pdo): array
    {
        return $pdo->query('SELECT * FROM przedmiot ORDER BY nazwa')->fetchAll();
    }

    public static function addClass(PDO $pdo, string $name, int $stopien): void
    {
        $stmt = $pdo->prepare('INSERT INTO klasa (nazwa, stopien) VALUES (:nazwa, :stopien)');
        $stmt->execute([':nazwa' => $name, ':stopien' => $stopien]);
    }

    public static function addSubject(PDO $pdo, string $name): void
    {
        $stmt = $pdo->prepare('INSERT INTO przedmiot (nazwa) VALUES (:nazwa)');
        $stmt->execute([':nazwa' => $name]);
    }

    public static function assignments(PDO $pdo): array
    {
        $sql = 'SELECT pwk.id_przedmiot_w_klasie, k.nazwa AS klasa, p.nazwa AS przedmiot, u.imie, u.nazwisko
                FROM przedmiot_w_klasie pwk
                JOIN klasa k ON k.id_klasy = pwk.id_klasy
                JOIN przedmiot p ON p.id_przedmiotu = pwk.id_przedmiotu
                JOIN nauczyciel n ON n.id_nauczyciela = pwk.id_nauczyciela
                JOIN uzytkownik u ON u.id_uzytkownika = n.id_uzytkownika
                ORDER BY k.nazwa, p.nazwa';
        return $pdo->query($sql)->fetchAll();
    }

    public static function addAssignment(PDO $pdo, int $classId, int $subjectId, int $teacherId, string $terminy): void
    {
        $stmt = $pdo->prepare('INSERT INTO przedmiot_w_klasie (id_klasy, id_przedmiotu, id_nauczyciela, terminy)
                               VALUES (:klasa, :przedmiot, :nauczyciel, :terminy)');
        $stmt->execute([
            ':klasa' => $classId,
            ':przedmiot' => $subjectId,
            ':nauczyciel' => $teacherId,
            ':terminy' => $terminy,
        ]);
    }

    public static function teachers(PDO $pdo): array
    {
        $sql = 'SELECT n.id_nauczyciela, u.imie, u.nazwisko
                FROM nauczyciel n
                JOIN uzytkownik u ON u.id_uzytkownika = n.id_uzytkownika
                ORDER BY u.nazwisko';
        return $pdo->query($sql)->fetchAll();
    }

    public static function parents(PDO $pdo): array
    {
        $sql = 'SELECT r.id_rodzica, u.imie, u.nazwisko, u.email
                FROM rodzic r
                JOIN uzytkownik u ON u.id_uzytkownika = r.id_uzytkownika
                ORDER BY u.nazwisko';
        return $pdo->query($sql)->fetchAll();
    }

    public static function students(PDO $pdo): array
    {
        $sql = 'SELECT ucz.id_ucznia, u.imie, u.nazwisko, k.nazwa AS klasa
                FROM uczen ucz
                JOIN uzytkownik u ON u.id_uzytkownika = ucz.id_uzytkownika
                LEFT JOIN klasa k ON k.id_klasy = ucz.id_klasy
                ORDER BY k.nazwa, u.nazwisko';
        return $pdo->query($sql)->fetchAll();
    }

    public static function assignParent(PDO $pdo, int $parentId, int $studentId): void
    {
        $stmt = $pdo->prepare('INSERT IGNORE INTO opieka (id_ucznia, id_rodzica) VALUES (:uczen, :rodzic)');
        $stmt->execute([':uczen' => $studentId, ':rodzic' => $parentId]);
    }

    public static function assignStudentToClass(PDO $pdo, int $studentId, int $classId): void
    {
        $stmt = $pdo->prepare('UPDATE uczen SET id_klasy = :klasa WHERE id_ucznia = :uczen');
        $stmt->execute([':klasa' => $classId, ':uczen' => $studentId]);
    }

    public static function addUser(PDO $pdo, array $data): int
    {
        $stmt = $pdo->prepare('INSERT INTO uzytkownik (login, haslo, imie, nazwisko, email, czy_aktywny, rola, telefon)
                               VALUES (:login, :haslo, :imie, :nazwisko, :email, "TAK", :rola, :telefon)');
        $stmt->execute($data);
        return (int) $pdo->lastInsertId();
    }

    public static function addTeacher(PDO $pdo, int $userId): void
    {
        $stmt = $pdo->prepare('INSERT INTO nauczyciel (id_uzytkownika) VALUES (:id)');
        $stmt->execute([':id' => $userId]);
    }

    public static function addParent(PDO $pdo, int $userId): void
    {
        $stmt = $pdo->prepare('INSERT INTO rodzic (id_uzytkownika) VALUES (:id)');
        $stmt->execute([':id' => $userId]);
    }

    public static function addStudent(PDO $pdo, int $userId, int $classId, int $nrDziennika): void
    {
        $stmt = $pdo->prepare('INSERT INTO uczen (nr_dziennika, id_uzytkownika, id_klasy) VALUES (:nr, :uid, :klasa)');
        $stmt->execute([':nr' => $nrDziennika, ':uid' => $userId, ':klasa' => $classId]);
    }
}
