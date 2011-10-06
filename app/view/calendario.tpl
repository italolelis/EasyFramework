{include file=$header}
<header class="container_12 clearfix">
    <div class="grid_12">
        <h1>Calendário de Agendamentos</h1>
    </div>
</header>
<section class="container_12 clearfix">
    <div class="grid_12">
        <div id="fullcalendar"></div>
        <div class="message info leading">
            To learn more about this great calendar app, <a href="http://arshaw.com/fullcalendar/">click here</a>
        </div>
    </div>
</section>

<!-- FULLCALENDAR -->
<script type="text/javascript" src="lib/fullcalendar/jquery-ui-interactions.min.js"></script>
<script type="text/javascript" src="lib/fullcalendar/fullcalendar.min.js"></script>
{literal}
    <script type="text/javascript">
            $(document).ready(function() {

                    $('#fullcalendar').fullCalendar({
                            header: {
                                    left: 'prev,next today',
                                    center: 'title',
                                    right: 'month,agendaWeek,agendaDay'
                            },
                            editable: true,
                            timeFormat: 'H:mm', // uppercase H for 24-hour clock
                            events: [
    {/literal}
    {foreach $datas as $data}
        {$data}
    {/foreach}
    {literal}
                            ]
                    });
		
            });
    </script>
{/literal}

{include file=$footer}
