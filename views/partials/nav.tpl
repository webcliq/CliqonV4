<div class="header">
    <div class="home-menu pure-menu pure-menu-horizontal pure-menu-fixed">
        
        <ul class="pure-menu-list">
            <li class="pure-menu-item"><a href="/page/@($idiom)/index/" class="pure-menu-link">@(Q::uStr('30:Home'))</a></li>
            <li class="pure-menu-item"><a href="http://cliq.help/" target="_blank" class="pure-menu-link">@(Q::uStr('32:Documentation'))</a></li>
            <li class="pure-menu-item"><a href="/admindesktop/@($idiom)/dashboard/" class="pure-menu-link">@(Q::uStr('31:Administration'))</a></li>

            <li class="pure-menu-item">
            	@if($idiom == 'es')
            	<span class="circle-text pointer languagebutton redc" title="Cambiar idioma a Español" data-idiom="es">es</span>
				<span class="circle-text pointer languagebutton whitec" title="@(Q::uStr('33:Change language to English'))" data-idiom="en">en</span>
				@endif
            	@if($idiom == 'en')
            	<span class="circle-text pointer languagebutton whitec" title="Cambiar idioma a Español" data-idiom="es">es</span>
				<span class="circle-text pointer languagebutton redc" title="@(Q::uStr('33:Change language to English'))" data-idiom="en">en</span>
				@endif
			</li>
        </ul>

        <a class="pure-menu-heading " href="/" style=""><img class="brand-image" src="/views/img/logo_sm.png" /></a>

    </div>
</div>