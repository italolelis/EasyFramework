<table>
    <tbody>
        <tr>
            <th>Versão do EasyFramework</th>
            <td><?= App::getVersion() ?></td>
        </tr>
        <tr>
            <th>Ambiente</th>
            <td><?= Config::read("environment") ?></td>
        </tr>
        <tr>
            <th>Versão do PHP</th>
            <td><?= phpversion() ?></td>
        </tr>
        <?php if (function_exists("apache_get_version")): ?>
            <tr>
                <th>Servidor</th>
                <td><?= apache_get_version() ?></td>
            </tr>
        <?php endif ?>
        <tr>
            <th>Caminho raiz</th>
            <td><?= ROOT ?></td>
        </tr>

    </tbody>
</table> 