{include file=$header}
<header class="container_12 clearfix">
    <div class="grid_12">
        <h1>Agendamentos</h1>
    </div>
</header>
<section class="container_12 clearfix">
    <section>
        <table class="display" id="example"> 
            <thead> 
                <tr> 
                    <th>ID</th>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                {foreach $lista_agendamento as $agendamento}
                    <tr class="{if $agendamento->getStatus() == 'AGENDADO'}gradeA{else}gradeX{/if}"> 
                        <td class="center">{$agendamento->getId()}</td> 
                        <td class="center">{$agendamento->getDia()}</td> 
                        <td class="center">{$agendamento->getHora()}</td> 
                        <td class="center">{$agendamento->getStatus()}</td> 
                        <td class="center"><button onclick="location.href = '{$url.editarAgendamento}{$agendamento->getId()}'; return false;">Editar</button></td> 
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
                agendamento = new Crud('agendamento', '#example', '')
            }); 
    </script> 
{/literal}
<!-- DATATABLES END -->
{include file=$footer}