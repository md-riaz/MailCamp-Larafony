<?php

namespace App\Database;

use PDO;

/**
 * Base Model Class
 * Simple ORM for database operations
 */
class Model
{
    protected static $connection;
    protected $table;
    protected $fillable = [];
    protected $hidden = [];
    protected $attributes = [];
    public $id;
    
    /**
     * Get database connection
     */
    protected static function getConnection()
    {
        if (!self::$connection) {
            $config = require __DIR__ . '/../../config/database.php';
            $dbConfig = $config['connections'][$config['default']];
            
            $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
            self::$connection = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        }
        
        return self::$connection;
    }
    
    /**
     * Find by ID
     */
    public static function findById($id)
    {
        $instance = new static();
        $pdo = self::getConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM {$instance->table} WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result) {
            return self::hydrate($result);
        }
        
        return null;
    }
    
    /**
     * Find by column value
     */
    public static function findBy($column, $value)
    {
        $instance = new static();
        $pdo = self::getConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM {$instance->table} WHERE {$column} = ? LIMIT 1");
        $stmt->execute([$value]);
        $result = $stmt->fetch();
        
        if ($result) {
            return self::hydrate($result);
        }
        
        return null;
    }
    
    /**
     * Find by email
     */
    public static function findByEmail($email)
    {
        return self::findBy('email', $email);
    }
    
    /**
     * Find by token
     */
    public static function findByToken($token)
    {
        return self::findBy('unsubscribe_token', $token);
    }
    
    /**
     * Find all by organization
     */
    public static function findByOrganization($organizationId, $orderBy = 'id', $order = 'DESC')
    {
        $instance = new static();
        $pdo = self::getConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM {$instance->table} WHERE organization_id = ? ORDER BY {$orderBy} {$order}");
        $stmt->execute([$organizationId]);
        $results = $stmt->fetchAll();
        
        return array_map(function($result) {
            return self::hydrate($result);
        }, $results);
    }
    
    /**
     * Find all by campaign
     */
    public static function findByCampaign($campaignId)
    {
        $instance = new static();
        $pdo = self::getConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM {$instance->table} WHERE campaign_id = ?");
        $stmt->execute([$campaignId]);
        $results = $stmt->fetchAll();
        
        return array_map(function($result) {
            return self::hydrate($result);
        }, $results);
    }
    
    /**
     * Get next available queue job
     */
    public static function getNextAvailable()
    {
        $instance = new static();
        $pdo = self::getConnection();
        
        $stmt = $pdo->prepare("
            SELECT * FROM {$instance->table} 
            WHERE status = 'pending' 
            AND available_at <= NOW() 
            ORDER BY id ASC 
            LIMIT 1
        ");
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result) {
            return self::hydrate($result);
        }
        
        return null;
    }
    
    /**
     * Get recent campaigns
     */
    public static function getRecentByOrganization($organizationId, $limit = 10)
    {
        $instance = new static();
        $pdo = self::getConnection();
        
        $stmt = $pdo->prepare("
            SELECT * FROM {$instance->table} 
            WHERE organization_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$organizationId, $limit]);
        $results = $stmt->fetchAll();
        
        return array_map(function($result) {
            return self::hydrate($result);
        }, $results);
    }
    
    /**
     * Count by organization
     */
    public static function countByOrganization($organizationId)
    {
        $instance = new static();
        $pdo = self::getConnection();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM {$instance->table} WHERE organization_id = ?");
        $stmt->execute([$organizationId]);
        $result = $stmt->fetch();
        
        return $result->count ?? 0;
    }
    
    /**
     * Count active campaigns by organization
     */
    public static function countActiveByOrganization($organizationId)
    {
        $instance = new static();
        $pdo = self::getConnection();
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM {$instance->table} 
            WHERE organization_id = ? 
            AND status IN ('sending', 'scheduled')
        ");
        $stmt->execute([$organizationId]);
        $result = $stmt->fetch();
        
        return $result->count ?? 0;
    }
    
    /**
     * Save (insert or update)
     */
    public function save()
    {
        if ($this->id) {
            return $this->updateRecord();
        } else {
            return $this->insertRecord();
        }
    }
    
    /**
     * Insert new record
     */
    protected function insertRecord()
    {
        $pdo = self::getConnection();
        
        $data = [];
        foreach ($this->fillable as $field) {
            if (property_exists($this, $field)) {
                $data[$field] = $this->$field;
            }
        }
        
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($data));
        
        $this->id = $pdo->lastInsertId();
        
        return $this;
    }
    
    /**
     * Update existing record
     */
    protected function updateRecord()
    {
        $pdo = self::getConnection();
        
        $data = [];
        foreach ($this->fillable as $field) {
            if (property_exists($this, $field)) {
                $data[$field] = $this->$field;
            }
        }
        
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "{$column} = ?";
        }
        $setClause = implode(', ', $sets);
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = ?";
        $values = array_values($data);
        $values[] = $this->id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        return $this;
    }
    
    /**
     * Update with data array
     */
    public function update($data)
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->$key = $value;
            }
        }
        
        return $this->save();
    }
    
    /**
     * Create new record
     */
    public function create($data)
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->$key = $value;
            }
        }
        
        return $this->save();
    }
    
    /**
     * Delete record
     */
    public function delete()
    {
        if (!$this->id) {
            return false;
        }
        
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->execute([$this->id]);
        
        return true;
    }
    
    /**
     * Hydrate model from database result
     */
    protected static function hydrate($data)
    {
        $instance = new static();
        
        foreach ($data as $key => $value) {
            $instance->$key = $value;
        }
        
        return $instance;
    }
    
    /**
     * Magic getter
     */
    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }
    
    /**
     * Magic setter
     */
    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }
    
    /**
     * Check if property exists
     */
    public function __isset($name)
    {
        return isset($this->attributes[$name]);
    }
    
    /**
     * Relationship: belongs to
     */
    public function belongsTo($relatedClass)
    {
        // Simple relationship - just return the class for now
        return new $relatedClass();
    }
    
    /**
     * Relationship: has many
     */
    public function hasMany($relatedClass, $foreignKey = null)
    {
        // Simple relationship - return empty array
        return [];
    }
    
    /**
     * Relationship: has one
     */
    public function hasOne($relatedClass)
    {
        // Simple relationship - return new instance
        return new $relatedClass();
    }
}
