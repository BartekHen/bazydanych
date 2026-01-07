<?php

class ScheduleModel
{
    public static function scheduleForClass(PDO $pdo, int $classId): array
    {
        $sql = 'SELECT pwk.terminy, p.nazwa AS przedmiot
                FROM przedmiot_w_klasie pwk
                JOIN przedmiot p ON p.id_przedmiotu = pwk.id_przedmiotu
                WHERE pwk.id_klasy = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $classId]);
        $rows = $stmt->fetchAll();

        $schedule = [];
        foreach ($rows as $row) {
            if (!$row['terminy']) {
                continue;
            }
            $slots = explode(',', $row['terminy']);
            foreach ($slots as $slot) {
                [$day, $hour] = array_map('intval', explode('_', $slot));
                $schedule[$day][$hour][] = $row['przedmiot'];
            }
        }

        return $schedule;
    }
}
