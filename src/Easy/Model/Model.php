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

namespace Easy\Model;

use Easy\Core\Object;
use Easy\Model\ORM\Relations\Relation;
use Easy\Serializer\JsonEncoder;

/**
 * Object-relational mapper.
 *
 * DBO-backed object data model.
 * Automatically selects a database table name based on a pluralized lowercase object class name
 * (i.e. class 'User' => table 'users'; class 'Man' => table 'men')
 * The table is required to have at least 'id auto_increment' primary key.
 * 
 * @since 0.2
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
abstract class Model extends Object implements IModel
{

    protected $modelState;

    public function __construct()
    {
        parent::__construct();
        $this->modelState = new ModelState();
    }

    public function __isset($name)
    {
        $relation = new Relation($this);
        return $relation->buildRelations($name);
    }

    public function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
    }

    public function toJSON()
    {
        return JsonEncoder::encode($this);
    }

    /**
     * {@inheritdoc}
     */
    public function afterFind()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDelete($cascade = true)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        return null;
    }

}

