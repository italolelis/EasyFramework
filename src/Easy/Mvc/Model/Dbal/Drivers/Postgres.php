<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Model\Dbal\Drivers;

use Easy\Mvc\Model\Dbal\Exceptions\MissingConnectionException;
use PDO;
use PDOException;

/**
 * The Postgres driver using PDO
 *
 * @since 2.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class Postgres extends PdoDriver
{

    /**
     * Base configuration settings for Postgres driver
     * @var array
     */
    protected $baseConfig = array(
        'persistent' => true,
        'host' => 'localhost',
        'login' => 'root',
        'password' => '',
        'database' => 'easy_db',
        'schema' => 'public',
        'port' => '5432',
        'flags' => array(),
        'encoding' => 'utf8',
        'dsn' => null
    );

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        $this->config = array_merge($this->baseConfig, $this->config);

        if (!$this->connection) {
            try {
                $this->config['flags'] += array(
                    PDO::ATTR_PERSISTENT => $this->config['persistent'],
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                );

                if (empty($this->config['dsn'])) {
                    $this->config['dsn'] = "pgsql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']}";
                }

                $this->connection = new PDO(
                        $this->config['dsn'], $this->config['user'], $this->config['password'], $this->config['flags']
                );

                if (!empty($this->config['encoding'])) {
                    $this->setEncoding($this->config['encoding']);
                }
                if (!empty($this->config['schema'])) {
                    $this->setSchema($this->config['schema']);
                }
            } catch (PDOException $e) {
                throw new MissingConnectionException(array('class' => $e->getMessage()));
            }
        }

        return $this->connection;
    }

    /**
     * Sets connection encoding
     *
     * @return void
     * */
    public function setEncoding($encoding)
    {
        $this->connection->exec('SET NAMES ' . $this->connection->quote($encoding));
    }

    /**
     * Sets connection default schema, if any relation defined in a query is not fully qualified
     * postgres will fallback to looking the relation into defined default schema
     *
     * @return void
     * */
    public function setSchema($schema)
    {
        $this->connection->exec('SET search_path TO ' . $this->connection->quote($schema));
    }

    /**
     * {@inheritdoc}
     */
    public function listTables()
    {
        $query = $this->connection->prepare('SHOW TABLES FROM ' . $this->config['database']);
        $query->setFetchMode(PDO::FETCH_NUM);
        $query->execute();

        while ($source = $query->fetch()) {
            $sources [] = $source[0];
        }

        return $sources;
    }

    /**
     * {@inheritdoc}
     */
    public function listColumns($table)
    {
        $result = $this->execute('SHOW COLUMNS FROM ' . $table);
        $columns = $this->fetchAll($result, null, PDO::FETCH_ASSOC);
        $schema = array();

        foreach ($columns as $column) {
            $schema[$column['Field']] = array(
                'key' => $column['Key']
            );
        }

        return $schemas[$table] = $schema;
    }

    /**
     * {@inheritdoc}
     */
    public static function enabled()
    {
        return in_array('sqlite', PDO::getAvailableDrivers());
    }

    /**
     * {@inheritdoc}
     */
    public function getLastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * {@inheritdoc}
     */
    public function getAffectedRows()
    {
        return $this->result->rowCount();
    }

}

