<?php

class TeacherModel
{
    public static function findTeacherId(PDO $pdo, int $userId): ?int
    {
        $stmt = $pdo->prepare('SELECT id_nauczyciela FROM nauczyciel WHERE id_uzytkownika = :uid');
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch();
        return $row ? (int) $row['id_nauczyciela'] : null;
    }

    public static function assignments(PDO $pdo, int $teacherId): array
    {
        $sql = 'SELECT pwk.id_przedmiot_w_klasie, k.id_klasy, k.nazwa AS klasa, p.nazwa AS przedmiot
                FROM przedmiot_w_klasie pwk
                JOIN klasa k ON k.id_klasy = pwk.id_klasy
                JOIN przedmiot p ON p.id_przedmiotu = pwk.id_przedmiotu
                WHERE pwk.id_nauczyciela = :id
                ORDER BY k.nazwa, p.nazwa';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $teacherId]);
        return $stmt->fetchAll();
    }
}
