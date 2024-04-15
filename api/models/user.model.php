<?php
namespace Model;

require __DIR__ . "/../database.php";

use Database;
use PDOException;

final class UserModelFields
{
    public const TABLE_NAME = "fred_users_tb";
}

class UserModel
{
    public ?int $id;
    public string $username;
    public string $password;
    public ?string $profilePic = null;

    public function __construct(
        private Database $db,
    ) {
    }

    public function get_by_id(int $id): ?UserModel
    {
        $query = $this->db->connect()->prepare("SELECT * FROM " . UserModelFields::TABLE_NAME . " WHERE `id`=?;");
        $query->execute([$id]);

        $data = $query->fetchAll();
        return $this->collect($data);
    }

    public function get_by_account(): ?UserModel
    {
        $query = $this->db->connect()->prepare("SELECT * FROM " . UserModelFields::TABLE_NAME . " WHERE `name`=? AND `password`=?;");
        $query->execute([$this->username, $this->password]);

        $data = $query->fetchAll();
        return $this->collect($data);
    }

    // TODO - Return custom error for handing client input errors
    public function insert(): bool
    {
        $query = $this->db->connect()->prepare(
            "INSERT INTO " . UserModelFields::TABLE_NAME .
            "(`id`, `name`, `password`, `profile-pic`)" .
            "VALUES (null, ?, ?, ?)"
        );

        try {
            return $query->execute([$this->username, $this->password, $this->profilePic]);
        } catch (PDOException $e) {
            // Env or Prod
            echo $e->getMessage();

            return false;
        }
    }

    public function update(UserModel $user): ?UserModel
    {
        $query = $this->db->connect()->prepare(
            "UPDATE " . UserModelFields::TABLE_NAME .
            "SET `name`=?, `password`=?, `profile-pic`=?",
        );

        $query->execute([$user->username, $user->password, null]);
        $data = $query->fetchAll();

        return $this->collect($data);
    }

    public function update_image(): bool
    {
        $query = $this->db->connect()->prepare(
            "UPDATE " . UserModelFields::TABLE_NAME .
            " SET `profile-pic`=? WHERE `id`=?",
        );

        return $query->execute([$this->profilePic, $this->id]);
    }

    public function delete(int $id): bool
    {
        $query = $this->db->connect()->prepare(
            "DELETE FROM " . UserModelFields::TABLE_NAME .
            " WHERE `id`=?"
        );

        $query->execute([$id]);
        return $query->rowCount() > 0;
    }

    private function collect(?array $data): ?UserModel
    {
        if (empty($data) || empty($data[0])) {
            return null;
        }

        $model = new UserModel($this->db);
        $model->id = $data[0]["id"];
        $model->username = $data[0]["name"];
        $model->password = $data[0]["password"];
        $model->profilePic = $data[0]["profile-pic"];

        return $model;
    }
}
