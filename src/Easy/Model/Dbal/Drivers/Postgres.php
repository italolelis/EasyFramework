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

namespace Easy\Model\Dbal\Drivers;

use Easy\Model\Dbal\Exceptions\MissingConnectionException;
use Easy\Utility\Hash;
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
        $this->config = Hash::merge($this->baseConfig, $this->config);

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
                                $this->config['dsn'],
                                $this->config['user'],
                                $this->config['password'],
                                $this->config['flags']
                );

                if (!empty($config['encoding'])) {
                    $this->setEncoding($config['encoding']);
                }
                if (!empty($config['schema'])) {
                    $this->setSchema($config['schema']);
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

