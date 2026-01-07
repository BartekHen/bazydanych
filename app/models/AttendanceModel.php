<?php

class AttendanceModel
{
    public static function absencesForStudent(PDO $pdo, int $studentId): array
    {
        $sql = 'SELECT n.usprawiedliwiona, n.powod, l.data, l.temat, p.nazwa AS przedmiot
                FROM nieobecnosc n
                JOIN lekcja l ON l.id_lekcji = n.id_lekcji
                JOIN przedmiot_w_klasie pwk ON pwk.id_przedmiot_w_klasie = l.id_przedmiot_w_klasie
                JOIN przedmiot p ON p.id_przedmiotu = pwk.id_przedmiotu
                WHERE n.id_ucznia = :id
                ORDER BY l.data DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $studentId]);
        return $stmt->fetchAll();
    }

    public static function addAbsence(PDO $pdo, int $studentId, int $lessonId): void
    {
        $stmt = $pdo->prepare('INSERT INTO nieobecnosc (id_ucznia, id_lekcji, usprawiedliwiona, powod) VALUES (:uczen, :lekcja, 0, "Nieobecny")');
        $stmt->execute([':uczen' => $studentId, ':lekcja' => $lessonId]);
    }

    public static function deleteAbsencesForLesson(PDO $pdo, int $lessonId): void
    {
        $stmt = $pdo->prepare('DELETE FROM nieobecnosc WHERE id_lekcji = :lekcja');
        $stmt->execute([':lekcja' => $lessonId]);
    }
}
