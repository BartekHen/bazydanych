<?php

class TestsModel
{
    public static function testsForClass(PDO $pdo, int $classId): array
    {
        $sql = 'SELECT s.temat, s.data, s.godzina, p.nazwa AS przedmiot
                FROM sprawdzian s
                JOIN przedmiot_w_klasie pwk ON pwk.id_przedmiot_w_klasie = s.id_przedmiot_w_klasie
                JOIN przedmiot p ON p.id_przedmiotu = pwk.id_przedmiotu
                WHERE pwk.id_klasy = :id
                ORDER BY s.data, s.godzina';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $classId]);
        return $stmt->fetchAll();
    }

    public static function testsForTeacher(PDO $pdo, int $teacherId): array
    {
        $sql = 'SELECT s.temat, s.data, s.godzina, k.nazwa AS klasa, p.nazwa AS przedmiot
                FROM sprawdzian s
                JOIN przedmiot_w_klasie pwk ON pwk.id_przedmiot_w_klasie = s.id_przedmiot_w_klasie
                JOIN klasa k ON k.id_klasy = pwk.id_klasy
                JOIN przedmiot p ON p.id_przedmiotu = pwk.id_przedmiotu
                WHERE pwk.id_nauczyciela = :id
                ORDER BY s.data, s.godzina';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $teacherId]);
        return $stmt->fetchAll();
    }

    public static function addTest(PDO $pdo, array $data): void
    {
        $sql = 'INSERT INTO sprawdzian (id_przedmiot_w_klasie, temat, data, godzina)
                VALUES (:pwk, :temat, :data, :godzina)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':pwk' => $data['id_przedmiot_w_klasie'],
            ':temat' => $data['temat'],
            ':data' => $data['data'],
            ':godzina' => $data['godzina'],
        ]);
    }
}
