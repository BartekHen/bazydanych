<?php

class StudentModel
{
    public static function findByUserId(PDO $pdo, int $userId): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM uczen WHERE id_uzytkownika = :id');
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function childrenForParent(PDO $pdo, int $parentUserId): array
    {
        $sql = 'SELECT u.id_uzytkownika, u.imie, u.nazwisko, ucz.id_ucznia, ucz.id_klasy, k.nazwa AS klasa
                FROM rodzic r
                JOIN opieka o ON o.id_rodzica = r.id_rodzica
                JOIN uczen ucz ON ucz.id_ucznia = o.id_ucznia
                JOIN uzytkownik u ON u.id_uzytkownika = ucz.id_uzytkownika
                LEFT JOIN klasa k ON k.id_klasy = ucz.id_klasy
                WHERE r.id_uzytkownika = :uid';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':uid' => $parentUserId]);
        return $stmt->fetchAll();
    }

    public static function studentsByClass(PDO $pdo, int $classId): array
    {
        $sql = 'SELECT ucz.id_ucznia, ucz.nr_dziennika, u.imie, u.nazwisko
                FROM uczen ucz
                JOIN uzytkownik u ON u.id_uzytkownika = ucz.id_uzytkownika
                WHERE ucz.id_klasy = :id
                ORDER BY ucz.nr_dziennika, u.nazwisko';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $classId]);
        return $stmt->fetchAll();
    }
}
