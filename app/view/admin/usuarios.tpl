{include file=$header}
<header class="container_12 clearfix">
    <div class="grid_12">
        <h1>Usuários</h1>
    </div>
</header>
<section class="container_12 clearfix">
    <section>
        <table class="display" id="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Username</th>
                    <th>Grupo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                {foreach $usuarios as $usuario}
                    <tr class="gradeA">
                        <td class="center">{$usuario->getId()}</td>
                        <td class="center">{$usuario->getNome()}</td>
                        <td class="center">{$usuario->getUsername()}</td>
                        <td class="center">{$usuario->getAdmin()}</td>
                        <td class="center"><button onclick="location.href = '{$url.editarUsuario}{$usuario->getId()}'; return false;">Editar</button>|<button onclick="usuarios.deleteRegister('{$usuario->getId()}')">Excluir</button></td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </section>
</section>
<!-- DATATABLES -->
{literal}
    <script type="text/javascript" src="lib/datatables/js/jquery.dataTables.js"></script>
    <script type="text/javascript">
            $(document).ready(function(){
                //Propriedades do datatable
                $('#table').dataTable({
                    "aaSorting": [],
                    "oLanguage": {
                        "sUrl": "lib/datatables/dataTables.potuguese.txt"
                    },
                    "sPaginationType": "full_numbers"
                });
                    
                //objeto crud
                usuarios = new Crud('usuarios', '#table', '');
            });
    </script>
{/literal}
<!-- DATATABLES END -->
{include file=$footer}