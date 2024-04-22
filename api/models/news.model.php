<?php declare(strict_types=1);
namespace Model;

use Database;
use PDOException;

final class NewsModelFields
{
    public const TABLE_NAME = "fred_news_tb";
    public const ID = "id";
    public const TITLE = "title";
    public const DESCRIPTION = "description";
    public const CONTENT = "content";
    public const IMAGE = "image";
    public const CREATOR_ID = "creator_id_fk";
}

class NewsModel
{
    public ?int $id;
    public string $title;
    public string $description;
    public ?string $content;
    public ?string $image;
    public int $creator_id;

    public function __construct(
        private Database $db,
    ){}
 
    /**
     * TODO - Pagination
     * 
     * @return Self[]
     */
    public function get_all(): array
    {
        $query = $this->db->connect()->prepare("SELECT * FROM " . NewsModelFields::TABLE_NAME);
        $query->execute();

        $data = $query->fetchAll();

        return array_map(fn ($index) => $this->iterator_collect($data, $index), array_keys($data));
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

        return $this->iterator_collect($data);
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

    private function iterator_collect(?array $data, int $iterator = 0): ?Self
    {
        if (empty($data) || empty($data[0])) {
            return null;
        }

        $model = new NewsModel($this->db);
        $model->id = $data[$iterator][NewsModelFields::ID];
        $model->title = $data[$iterator][NewsModelFields::TITLE];
        $model->content = $data[$iterator][NewsModelFields::CONTENT];
        $model->description = $data[$iterator][NewsModelFields::DESCRIPTION];
        $model->image = $data[$iterator][NewsModelFields::IMAGE];
        $model->creator_id = $data[$iterator][NewsModelFields::CREATOR_ID];

        return $model;
    }
}