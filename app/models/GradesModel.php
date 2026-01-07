<?php

class GradesModel
{
    public static function gradesForStudent(PDO $pdo, int $studentId): array
    {
        $sql = 'SELECT o.wartosc, o.waga, o.data_wystawienia, o.typ, p.nazwa AS przedmiot
                FROM ocena o
                JOIN przedmiot_w_klasie pwk ON pwk.id_przedmiot_w_klasie = o.id_przedmiot_w_klasie
                JOIN przedmiot p ON p.id_przedmiotu = pwk.id_przedmiotu
                WHERE o.id_ucznia = :id
                ORDER BY p.nazwa, o.data_wystawienia DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $studentId]);
        return $stmt->fetchAll();
    }

    public static function addGrade(PDO $pdo, array $data): void
    {
        $sql = 'INSERT INTO ocena (wartosc, waga, data_wystawienia, typ, id_ucznia, id_przedmiot_w_klasie)
                VALUES (:wartosc, :waga, :data, :typ, :uczen, :pwk)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':wartosc' => $data['wartosc'],
            ':waga' => $data['waga'],
            ':data' => $data['data_wystawienia'],
            ':typ' => $data['typ'],
            ':uczen' => $data['id_ucznia'],
            ':pwk' => $data['id_przedmiot_w_klasie'],
        ]);
    }
}
