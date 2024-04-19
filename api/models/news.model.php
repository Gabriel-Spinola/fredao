<?php 
namespace Model;

require __DIR__ . "/../database.php";

use Database;
use PDOException;

final class NewsModelFields
{
    public const TABLE_NAME = "fred_news_tb";
    public const TITLE = "title";
    public const DESCRIPTION = "description";
    public const CONTENT = "content";
    public const IMAGE = "image";
    public const ID = "id";
} 

class NewsModel 
{
    public ?int $id;
    public string $title;
    public string $description;
    public ?string $content;
    public ?string $image;

    public function __construct(private Database $db) {}

    public function get_by_id(int $id): ?Self
    {
        $query = $this->db->connect()->prepare("SELECT * FROM " . NewsModelFields::TABLE_NAME . " WHERE `id`=?;");
        $query->execute([$id]);

        $data = $query->fetchAll();
        return $this->collect($data);
    }

    /**
     * @return ?Self[]
     */
    public function get_all(): ?array
    {
        $query = $this->db->connect()->prepare("SELECT * FROM " . NewsModelFields::TABLE_NAME);

        $data = $query->fetchAll();
        if (!$data) {
            return null;
        }

        /** @var Self[] */
        $result = array();
        for ($i = 0; $i < count($data); $i++) {
            array_push($result, $this->collect($data)); 
        }

        return $result;
    }

    // TODO - Return custom error for handing client input errors
    public function insert(): bool
    {
        $query = $this->db->connect()->prepare(
            "INSERT INTO " . UserModelFields::TABLE_NAME .
            "(`id`," .
                NewsModelFields::TITLE . ", " .
                NewsModelFields::DESCRIPTION . ", " . 
                NewsModelFields::CONTENT . ", "  .
                NewsModelFields::IMAGE . 
            ")" .
            "VALUES (null, ?, ?, ?)"
        );

        try {
            return $query->execute([$this->title, $this->description, $this->content, $this->image]);
        } catch (PDOException $e) {
            // Env or Prod
            echo $e->getMessage();

            return false;
        }
    }

    /// TODO - 
    public function update(UserModel $user): ?Self
    {
        assert(false, "Not implemented");

        $query = $this->db->connect()->prepare(
            "UPDATE " . UserModelFields::TABLE_NAME .
            "SET `name`=?, `password`=?, `profile-pic`=?",
        );

        $query->execute([$user->username, $user->password, null]);
        $data = $query->fetchAll();

        return $this->collect($data);
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

    private function collect(?array $data): ?Self
    {
        if (empty($data) || empty($data[0])) {
            return null;
        }

        $model = new NewsModel($this->db);
        $model->id = $data[0][NewsModelFields::ID];
        $model->title = $data[0][NewsModelFields::TITLE];
        $model->description = $data[0][NewsModelFields::DESCRIPTION];
        $model->profilePic = $data[0][NewsModelFields::IMAGE];

        return $model;
    }
}