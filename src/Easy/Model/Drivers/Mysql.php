<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.net)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.net)
 * @since         EasyFramework v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Easy\Model\Drivers;

use Easy\Error\MissingConnectionException;
use Easy\Utility\Hash;
use PDO;
use PDOException;

class Mysql extends PdoDriver
{

    /**
     * Base configuration settings for MySQL driver
     * @var array
     */
    protected $baseConfig = array(
        'persistent' => true,
        'host' => 'localhost',
        'login' => 'root',
        'password' => '',
        'database' => 'easy_db',
        'port' => '3306',
        'flags' => array(),
        'encoding' => 'utf8',
        'dsn' => null
    );

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        $this->config = Hash::merge($this->baseConfig, $this->config);

        if (!$this->connection) {
            try {
                $this->config['flags'] += array(
                    PDO::ATTR_PERSISTENT => $this->config['persistent'],
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                );

                if (empty($this->config['dsn'])) {
                    if (empty($this->config['unix_socket'])) {
                        $this->config['dsn'] = "mysql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']};charset={$this->config['encoding']}";
                    } else {
                        $this->config['dsn'] = "mysql:unix_socket={$this->config['unix_socket']};dbname={$this->config['database']}";
                    }
                }

                $this->connection = new PDO(
                                $this->config['dsn'],
                                $this->config['user'],
                                $this->config['password'],
                                $this->config['flags']
                );
            } catch (PDOException $e) {
                throw new MissingConnectionException(array('class' => $e->getMessage()));
            }
        }

        return $this->connection;
    }

    public function listSources()
    {
        $query = $this->connection->prepare('SHOW TABLES FROM ' . $this->config['database']);
        $query->setFetchMode(PDO::FETCH_NUM);
        $query->execute();

        while ($source = $query->fetch()) {
            $sources [] = $source[0];
        }

        return $sources;
    }

    public function describe($table)
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

    public function enabled()
    {
        return in_array('mysql', PDO::getAvailableDrivers());
    }

}

