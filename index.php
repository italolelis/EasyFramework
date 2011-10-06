<?php

/**
 *  Esse é o arquivo de entrada para todas as requisições do EasyFramework. A partir
 *  daqui todos os arquivos necessários são carregados e a mágica começa.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework
 *
 */
/**
 *  Inclui o arquivo de inicialização de todos os arquivos necesários para o
 *  funcionamento de sua aplicação.
 */
require_once 'core/boot.php';

$dispatcher = new Dispatcher();
$dispatcher->dispatch();
?>