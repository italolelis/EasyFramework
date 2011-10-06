<?php

/**
 * Interface que representa as operações do CRUD
 * @author Italo
 */
interface ICrud {

    function add();

    function update();

    function delete($options);

    function getAll();

    function getById($id);
}

/**
 * Interface que representa as operações feitas via Ajax
 * @author Italo
 */
interface IAjaxCrud {

    function add();

    function update();

    function delete();
}

?>
