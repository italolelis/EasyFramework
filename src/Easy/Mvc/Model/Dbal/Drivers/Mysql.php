<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.easyframework.net>.
 */

namespace Easy\Mvc\Model\Dbal\Drivers;

use Easy\Mvc\Model\Dbal\Exceptions\MissingConnectionException;
use Easy\Utility\Hash;
use PDO;
use PDOException;

/**
 * The Mysql driver using PDO
 *
 * @since 2.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
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
     * @inheritdoc
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
                        $this->config['dsn'], $this->config['user'], $this->config['password'], $this->config['flags']
                );

                if (!empty($this->config['encoding'])) {
                    $this->setEncoding($this->config['encoding']);
                }
            } catch (PDOException $e) {
                throw new MissingConnectionException(array('class' => $e->getMessage()));
            }
        }

        return $this->connection;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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

    private function setEncoding($encoding)
    {
        return $this->connection->exec('SET NAMES ' . $this->connection->quote($encoding));
    }

    /**
     * @inheritdoc
     */
    public static function enabled()
    {
        return in_array('mysql', PDO::getAvailableDrivers());
    }

    /**
     * @inheritdoc
     */
    public function getLastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * @inheritdoc
     */
    public function getAffectedRows()
    {
        return $this->result->rowCount();
    }

}

