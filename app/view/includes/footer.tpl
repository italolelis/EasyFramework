<footer class="container_12 clearfix">
    <div class="grid_12">
        Copyright &copy; 2011. Feito pela <a target="_blank" href="http://www.lellysinformatica.com">Lellys Informática</a>
    </div>
</footer>
</section>
<!-- Main Section End -->
</section>
</div>

<!-- MAIN JAVASCRIPTS -->
{$js.footer}
<!--[if lt IE 9]>
<script type="text/javascript" src="js/PIE.js"></script>
<script type="text/javascript" src="js/ie.js"></script>
<![endif]-->    
<!-- MAIN JAVASCRIPTS END -->

<!-- LOADING SCRIPT -->
{literal}
    <script>
    $(window).load(function(){
        $("#loading").fadeOut(function(){
            $(this).remove();
            $('body').removeAttr('style');
        });
    });
    </script>
{/literal}
<!-- LOADING SCRIPT -->
</body>
</html>