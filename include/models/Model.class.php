<?php

/**
 * Model: An abstract class to represent a database Model
 */
abstract class Model
{
    protected $db;
    protected $table;

    public function __construct($db, $table){
        $this->db = $db;
        $this->table = $table;
    }

    /**
     * Query the database with any query and return the result
     */
    protected function query($query, array $input_parameters=[]) {
        $statement = $this->db->prepare($query);

        $statement->execute($input_parameters);

        if ( strncasecmp($query, 'insert', 6) === 0 || strncasecmp($query, 'update', 6) === 0 )
            // cannot do fetchAll for insert and updat queries
            return true;

        return $statement->fetchAll(PDO::FETCH_ASSOC);

    }
    
    /**
     * Query the database with any query and return only the first row
     * (borrowed from the Cover website)
     */
    protected function query_first($query, array $input_parameters=[])
    {
        $result = $this->query($query, $input_parameters);
        
        if (is_string($result)) {
            /* Result is a string, this means an error occurred */
            return $result;
        } else if (!is_array($result) || count($result) == 0) {
            /* There are no results */
            return null;
        } else {
            /* Return the result */
            return $result[0];
        }
    }

    /**
     * Helper function to format SQL where conditions. Expects an array of arrays
     * of type [fieldname, operator, value], and an array to put fieldname => value
     * pairs in (to use in prepared statement)
     *
     * Returns a correctly formatted string with conditions
     */
    private function format_conditions(array $conditions, &$params) {
        $query = 'WHERE 1=1';

        foreach ($conditions as $condition) {
            $query .= ' AND ';
            $query .= '`' .$condition[0] . '` ' . $condition[1] . ' :where_' . $condition[0];
            $params['where_' . $condition[0]] = $condition[2];
        }

        return $query;
    }

    /**
     * Select data from table
     */
    public function get(array $conditions=[], $get_first=false) {
        $query = 'SELECT * FROM `' . $this->table . '`';
        $params = [];


        if (!empty($conditions))
            $query .= ' ' . $this->format_conditions($conditions, $params);

        if ($get_first)
            return $this->query_first($query, $params);
        return $this->query($query, $params);
    }


    /**
     * Select first entry from table matched by ID
     */
    public function get_by_id($id, $field='id') {
        return $this->get([[$field, '=', $id]], true);
    }


    /**
     * Insert one item into the DB
     */
    public function create($values) {
        $keys = array_keys($values);
        $placeholders = array_map(function ($k) { return ':'.$k; }, $keys);

        $query = 'INSERT INTO `' . $this->table . '` '.
                 '(`' .  implode('`, `', $keys) . '`) ' .
                 'VALUES (' .  implode(', ', $placeholders) . ');';

        $this->query($query, $values);
    }


    /**
     * Perform update with data and conditions
     */
    public function update(array $data, array $conditions=[]) {
        $query = 'UPDATE `' . $this->table . '` SET ';

        $params = [];

        $first = true;
        foreach ($data as $key => $value) {
            if ($first)
                $first = false;
            else
                $query .= ', ';
            $query .= '`' . $key . '` = :set_' . $key;
            $params['set_' . $key] = $value;
        }

        if (!empty($conditions))
            $query .= ' ' . $this->format_conditions($conditions, $params);

        $this->query($query, $params);
    }


    /**
     * Perform update for a specific id
     */
    public function update_by_id($id, array $data, $field='id') {
        $this->update($data, [[$field, '=', $id]]);
    }


    /**
     * Perform deletion
     */
    public function delete(array $conditions) {
        $query = 'DELETE FROM `' . $this->table . '`';
        
        $params = [];

        if (!empty($conditions))
            $query .= ' ' . $this->format_conditions($conditions, $params);
        else
            die('Delete without conditions is not allowed!');

        $this->query($query, $params);
    }


    /**
     * Perform deletion for a specific ID
     */
    public function delete_by_id($id, $field='id') {
        $this->delete([[$field, '=', $id]]);
    }
}
