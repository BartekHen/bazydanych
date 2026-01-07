<?php

class UserModel
{
    public static function findByLogin(PDO $pdo, string $login): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM uzytkownik WHERE login = :login AND czy_aktywny = "TAK"');
        $stmt->execute([':login' => $login]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findById(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM uzytkownik WHERE id_uzytkownika = :id');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function search(PDO $pdo, string $query, ?string $role): array
    {
        $sql = 'SELECT * FROM uzytkownik WHERE (imie LIKE :q OR nazwisko LIKE :q OR email LIKE :q)';
        $params = [':q' => '%' . $query . '%'];
        if ($role) {
            $sql .= ' AND rola = :rola';
            $params[':rola'] = $role;
        }
        $sql .= ' ORDER BY nazwisko, imie';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function updateRole(PDO $pdo, int $id, string $role): void
    {
        $stmt = $pdo->prepare('UPDATE uzytkownik SET rola = :rola WHERE id_uzytkownika = :id');
        $stmt->execute([':rola' => $role, ':id' => $id]);
    }

    public static function updatePassword(PDO $pdo, int $id, string $hash): void
    {
        $stmt = $pdo->prepare('UPDATE uzytkownik SET haslo = :haslo WHERE id_uzytkownika = :id');
        $stmt->execute([':haslo' => $hash, ':id' => $id]);
    }

    public static function deactivate(PDO $pdo, int $id): void
    {
        $stmt = $pdo->prepare('UPDATE uzytkownik SET czy_aktywny = "NIE" WHERE id_uzytkownika = :id');
        $stmt->execute([':id' => $id]);
    }

    public static function delete(PDO $pdo, int $id): void
    {
        $stmt = $pdo->prepare('DELETE FROM uzytkownik WHERE id_uzytkownika = :id');
        $stmt->execute([':id' => $id]);
    }
}
