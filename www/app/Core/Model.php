<?php

namespace App\Core;

use PDO;
use PDOException;
use stdClass;

abstract class Model
{
    protected string $entity;
    protected string $primary;
    protected array $required;
    protected bool $isUuid;
    protected ?PDOException $fail = null;
    public object $data;
    protected ?string $statement = null;
    protected ?array $params = null;
    protected ?string $group = null;
    protected ?string $order = null;
    protected ?string $limit = null;
    protected ?string $offset = null;

    public function __construct(
        string $entity,
        array $required,
        string $primary = 'id',
        bool $isUuid = false
    ) {
        $this->entity = $entity;
        $this->primary = $primary;
        $this->required = $required;
        $this->isUuid = $isUuid;
        $this->data = new stdClass();
    }

    public function __set($name, $value)
    {
        if (!isset($this->data)) {
            $this->data = new stdClass();
        }
        $this->data->$name = $value;
    }

    public function __get($name)
    {
        if (!isset($this->data)) {
            $this->data = new stdClass();
        }
        return $this->data->$name ?? null;
    }

    public function __isset($name)
    {
        if (!isset($this->data)) {
            $this->data = new stdClass();
        }
        return isset($this->data->$name);
    }

    public function data(): object
    {
        if (!isset($this->data)) {
            $this->data = new stdClass();
        }
        return $this->data;
    }

    public function fail(): ?PDOException
    {
        return $this->fail;
    }

    public function find(?string $terms = null, ?string $params = null, string $columns = "*"): static
    {
        if ($terms) {
            $this->statement = "SELECT {$columns} FROM {$this->entity} WHERE {$terms}";
            parse_str($params ?? "", $this->params);
        } else {
            $this->statement = "SELECT {$columns} FROM {$this->entity}";
            $this->params = [];
        }
        return $this;
    }

    public function findById($id, string $columns = "*"): ?static
    {
        return $this->find("{$this->primary} = :id", "id={$id}", $columns)->fetch();
    }

    public function group(string $column): static
    {
        $this->group = " GROUP BY {$column}";
        return $this;
    }

    public function order(string $columnOrder): static
    {
        $this->order = " ORDER BY {$columnOrder}";
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = " LIMIT {$limit}";
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = " OFFSET {$offset}";
        return $this;
    }

    public function fetch(bool $all = false, bool $readOnly = false): array|static|null
    {
        try {
            $sql = $this->statement . ($this->group ?? '') . ($this->order ?? '') . ($this->limit ?? '') . ($this->offset ?? '');
            $stmt = Connect::getInstance()->getConnection()->prepare($sql);
            $stmt->execute($this->params ?? []);
            if (!$stmt->rowCount()) {
                return null;
            }
            if ($all) {
                if ($readOnly) {
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $objects = [];
                foreach ($results as $result) {
                    $object = new static($this->entity, $this->required, $this->primary, $this->isUuid);
                    $object->data = (object)$result;
                    $objects[] = $object;
                }
                return $objects;
            }
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                if ($readOnly) {
                    return $result;
                }
                $this->data = (object)$result;
            }
            return $this;
        } catch (PDOException $exception) {
            $this->fail = $exception;
            return null;
        }
    }

    public function count(): int
    {
        $stmt = Connect::getInstance()->getConnection()->prepare($this->statement);
        $stmt->execute($this->params ?? []);
        return $stmt->rowCount();
    }

    public function save(): bool
    {
        $primary = $this->primary;
        $id = $this->data->$primary ?? null;
        $save = null;

        try {
            if (!$this->required()) {
                throw new PDOException("Preencha os campos necessários: " . implode(", ", $this->required));
            }
            // Update
            if (!empty($id)) {
                $save = $this->update($this->safe(), "{$this->primary} = :id", "id={$id}");
            }

            // Create
            if (empty($id)) {
                if ($this->isUuid) {
                    $this->data->$primary = $this->generateUuid();
                }
                $id = $this->create($this->safe());
                $save = $id;
            }

            if ($save === false) {
                return false;
            }

            $obj = $this->findById($id);
            if ($obj) {
                $this->data = $obj->data;
            }
            return true;
        } catch (PDOException $exception) {
            $this->fail = $exception;
            return false;
        }
    }

    public function destroy(): bool
    {
        $primary = $this->primary;
        $id = $this->data->$primary ?? null;
        if (empty($id)) {
            return false;
        }
        return $this->delete("{$this->primary} = :id", "id={$id}");
    }

    protected function required(): bool
    {
        $data = (array)$this->data();
        foreach ($this->required as $field) {
            if (empty($data[$field]) && !is_int($data[$field])) {
                return false;
            }
        }
        return true;
    }

    protected function safe(): ?array
    {
        $safe = (array)$this->data;
        if (!$this->isUuid) {
            unset($safe[$this->primary]);
        }
        return $safe;
    }

    protected function create(array $data)
    {
        if ($this->isUuid) {
            $data[$this->primary] = $this->data->{$this->primary};
        }
        $fields = implode(", ", array_keys($data));
        $values = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO {$this->entity} ({$fields}) VALUES ({$values})";
        $stmt = Connect::getInstance()->getConnection()->prepare($sql);
        $stmt->execute($data);
        return Connect::getInstance()->getConnection()->lastInsertId();
    }

    protected function update(array $data, string $terms, string $params)
    {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }
        $set = implode(", ", $set);
        $sql = "UPDATE {$this->entity} SET {$set} WHERE {$terms}";
        parse_str($params, $paramsArray);
        $stmt = Connect::getInstance()->getConnection()->prepare($sql);
        $stmt->execute(array_merge($data, $paramsArray));
        return $stmt->rowCount();
    }

    protected function delete(string $terms, string $params)
    {
        $sql = "DELETE FROM {$this->entity} WHERE {$terms}";
        parse_str($params, $paramsArray);
        $stmt = Connect::getInstance()->getConnection()->prepare($sql);
        $stmt->execute($paramsArray);
        return $stmt->rowCount();
    }

    protected function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
} 