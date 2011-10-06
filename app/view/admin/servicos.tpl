{include file=$header}
<header class="container_12 clearfix">
    <div class="grid_12">
        <h1>Servicos</h1>
    </div>
</header>
<section class="container_12 clearfix">
    <section>
        <table class="display" id="example"> 
            <thead> 
                <tr> 
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                {foreach $lista_servicos as $servico}
                    <tr class="gradeA"> 
                        <td class="center">{$servico->getId()}</td> 
                        <td class="center">{$servico->getNome()}</td> 
                        <td class="center"><button onclick="location.href = '{$url.editarServico}{$servico->getId()}'; return false;">Editar</button>|<button onclick="servico.deleteRegister('{$servico->getId()}')">Excluir</button></td> 
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
                $('#example').dataTable({
                    "bRetrieve": true,
                    "aaSorting": [],
                    "oLanguage": {
                        "sUrl": "lib/datatables/dataTables.potuguese.txt"
                    },
                    "sPaginationType": "full_numbers"
                });
                    
                //objeto crud
                servico = new Crud('servicos', '#example', '')
            }); 
    </script> 
{/literal}
<!-- DATATABLES END -->
{include file=$footer}