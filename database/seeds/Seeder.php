<?php
declare(strict_types=1);

namespace App\Core\Seeds;

use PDO;

abstract class Seeder
{
    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Run the seeder logic.
     */
    abstract public function run(): void;

    /**
     * Outputs log message to console.
     */
    protected function log(string $message): void
    {
        echo sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $message);
    }

    /**
     * Inserts a record using INSERT IGNORE.
     */
    protected function insertIgnore(string $table, array $data): bool
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);
        
        $sql = sprintf(
            "INSERT IGNORE INTO `%s` (%s) VALUES (%s)",
            $table,
            implode(', ', array_map(fn($col) => "`{$col}`", $columns)),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Upserts a record. Checks if key values already match. If they do, returns existing ID.
     * Otherwise inserts the record and returns the new ID.
     */
    protected function upsert(string $table, array $data, array $uniqueFields): int
    {
        $where = [];
        $params = [];
        foreach ($uniqueFields as $field) {
            $where[] = sprintf("`%s` = :check_%s", $field, $field);
            $params["check_{$field}"] = $data[$field];
        }

        $sqlCheck = sprintf("SELECT `id` FROM `%s` WHERE %s LIMIT 1", $table, implode(' AND ', $where));
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->execute($params);
        $id = $stmtCheck->fetchColumn();

        if ($id !== false) {
            // Update if different (optional, or just return existing ID for speed)
            $sets = [];
            $updateParams = ['id_val' => $id];
            foreach ($data as $key => $val) {
                if (!in_array($key, $uniqueFields, true)) {
                    $sets[] = sprintf("`%s` = :up_%s", $key, $key);
                    $updateParams["up_{$key}"] = $val;
                }
            }
            if ($sets) {
                $sqlUpdate = sprintf("UPDATE `%s` SET %s WHERE `id` = :id_val", $table, implode(', ', $sets));
                $stmtUpdate = $this->db->prepare($sqlUpdate);
                $stmtUpdate->execute($updateParams);
            }
            return (int)$id;
        }

        // Insert
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":ins_{$col}", $columns);
        $sqlInsert = sprintf(
            "INSERT INTO `%s` (%s) VALUES (%s)",
            $table,
            implode(', ', array_map(fn($col) => "`{$col}`", $columns)),
            implode(', ', $placeholders)
        );

        $insertParams = [];
        foreach ($data as $key => $val) {
            $insertParams["ins_{$key}"] = $val;
        }

        $stmtInsert = $this->db->prepare($sqlInsert);
        $stmtInsert->execute($insertParams);
        return (int)$this->db->lastInsertId();
    }
}
