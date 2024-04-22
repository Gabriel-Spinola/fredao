<?php declare(strict_types=1);
namespace Model;

use Database;
use PDOException;

final class Result 
{
    public function __construct(private \Exception|PDOException|null $error){}

    /**
     * @return \Exception|PDOException|false - If no errors found always returns false ortherwise return the expection
     */
    public function failed(): \Exception|PDOException|false 
    {
        if (!$this->error) {
            return false;
        }

        return $this->error;
    }

    public static function ok(): Result 
    {
        return new Self(null);
    }
}

final class NewsModelFields
{
    public const TABLE_NAME = "fred_news_tb";
    public const ID = "id";
    public const TITLE = "title";
    public const DESCRIPTION = "description";
    public const CONTENT = "content";
    public const IMAGE = "image";
    public const CREATOR_ID = "creator_id_fk";

    public const REQUIRED_FIELDS = array(Self::TITLE, Self::DESCRIPTION, Self::CONTENT, Self::IMAGE, Self::CREATOR_ID);
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
        private ?Database $db,
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
    public function insert(?PDOException &$exception = null): Result
    {
        try {
            $query = $this->db->connect()->prepare(
                "INSERT INTO " . NewsModelFields::TABLE_NAME .
                "(`id`," .
                    NewsModelFields::TITLE . ", " .
                    NewsModelFields::DESCRIPTION . ", " . 
                    NewsModelFields::CONTENT . ", "  .
                    NewsModelFields::IMAGE . ", "  .
                    NewsModelFields::CREATOR_ID . 
                ")" .
                "VALUES (null, ?, ?, ?, ?, ?)"
            );

            if (!$query->execute([$this->title, $this->description, $this->content, $this->image, $this->creator_id])) {
                throw new \Exception("Failed to execute insert query");
            }

            return Result::ok();
        } catch (PDOException|\Exception $e) {
            return new Result($e);
        }
    }

    /// TODO - 
    public function update(): ?Self
    {
        $query = $this->db->connect()->prepare(
            "UPDATE " . UserModelFields::TABLE_NAME .
            "SET `" . NewsModelFields::TITLE . "`=?," .
            "`" . NewsModelFields::DESCRIPTION . "`=?," . 
            "`" . NewsModelFields::CONTENT . "`=?" .
            "`" . NewsModelFields::IMAGE . "`=?"
        );

        $query->execute([$this->title, $this->description, $this->content, $this->image]);
        $data = $query->fetchAll();

        return $this->iterator_collect($data);
    }


    public function delete(int $id): bool
    {
        $query = $this->db->connect()->prepare(
            "DELETE FROM " . NewsModelFields::TABLE_NAME .
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