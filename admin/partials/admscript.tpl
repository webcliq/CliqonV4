<!-- AdmScript.Tpl  -->
<script>
var jlcd = '@($idiom)', lstr = [], str = [];
var sitepath = "http://"+document.location.hostname+"/";
var jspath = sitepath+"includes/js/";
var viewpath = sitepath+"admin/"; var jwt = "@raw($jwt)";
var ctrlDown = false, ctrlKey = 17, cmdKey = 91, vKey = 86, cKey = 67;
var jwt = "@raw($jwt)"; // This is now essential

// basket.clear(true);
basket.require(

	// Libraries
    {url: jspath+"library.js"},
    {url: viewpath+"js/adminlibrary.js"},
    {url: jspath+"vue.min.js"},
    {url: jspath+"tag-it.js"},
    {url: jspath+"phpjs.js"},
	{url: jspath+"prettyprint.js"},
    {url: jspath+"dropzone.js"},
    {url: jspath+"gijgo.min.js"},
    {url: jspath+"trumbowyg.min.js"},
	{url: jspath+"codemirror/lib/codemirror.js"},
	{url: jspath+"codemirror/addon/display/panel.js"},
	{url: jspath+"tinymce/tinymce.min.js"},
	{url: jspath+"dhtmlxscheduler.js"},
	{url: jspath+"galleria.js"},
	{url: jspath+"grapes.min.js"},
	{url: viewpath+"js/app.js"},

	// Cliqon Javascript language file - other JS translations could be included
	{url: jspath+"i18n/cliqon."+jlcd+".js"},   

    // Apps
    {url: viewpath+"js/admin.js"},
    {url: viewpath+"js/cliq.js"},
	{url: viewpath+"js/cliqd.js"},
	{url: viewpath+"js/cliqv.js"},
	{url: viewpath+"js/cliqr.js"},
	{url: viewpath+"js/cliqm.js"},
	{url: viewpath+"js/cliqf.js"},
	{url: viewpath+"plugins/cliqp.js"}

).then(function(msg) {


	$(this).ajaxStart(function() {
	    $('#loading').removeClass('loadinghide');
	    $('#loading').show();
	}).ajaxStop(function() {
	    $('#loading').fadeOut(500);
	});

	// Javascript language file load
    lstr = str[jlcd];
	Dropzone.autoDiscover = false;
	var sessid = Cookies.get('PHPSESSID');

    /* ---------- Place Bootstrap 4 loaders here ---------- */

        $('[data-toggle="popover"]').popover();	

	/* ---------- Main Menu Open/Close, Min/Full ---------- */

		//Main navigation variable
		$.navigation = $('nav > ul.nav');

		$.panelIconOpened = 'icon-arrow-up';
		$.panelIconClosed = 'icon-arrow-down';

		//Default colours
		$.brandPrimary =  '#20a8d8';
		$.brandSuccess =  '#4dbd74';
		$.brandInfo =     '#63c2de';
		$.brandWarning =  '#f8cb00';
		$.brandDanger =   '#f86c6b';

		$.grayDark =      '#2a2c36';
		$.gray =          '#55595c';
		$.grayLight =     '#818a91';
		$.grayLighter =   '#d1d4d7';
		$.grayLightest =  '#f8f9fa';  	

		// Add class .active to current link
		$.navigation.find('a').each(function(){
			var cUrl = String(window.location).split('?')[0];

			if (cUrl.substr(cUrl.length - 1) == '#') {
			  	cUrl = cUrl.slice(0,-1);
			}

			if ($($(this))[0].href==cUrl) {
			  	$(this).addClass('active');

			  	$(this).parents('ul').add(this).each(function(){
			    	$(this).parent().addClass('open');
			  	});
			}
		});

		// Dropdown Menu
		$.navigation.on('click', 'a', function(e) {
			if ($(this).hasClass('nav-dropdown-toggle')) {
			  	$(this).parent().toggleClass('open');
			  	resizeBroadcast();
			}
		});

		// Bootstrap 4 dropdowns
		$('.dropdown-toggle').dropdown();

		$('.sidebar-toggler').on('click', function(e){
			$('body').toggleClass('sidebar-hidden');
			resizeBroadcast();
		});

		$('.sidebar-minimizer').on('click', function(e){
			$('body').toggleClass('sidebar-minimized');
			resizeBroadcast();
		});

		$('.brand-minimizer').on('click', function(e){
			$('body').toggleClass('brand-minimized');
		});

		$('.aside-menu-toggler').on('click', function(e){
			$('body').toggleClass('aside-menu-hidden');
			resizeBroadcast();
		});

		$('.mobile-sidebar-toggler').on('click', function(e){
			$('body').toggleClass('sidebar-mobile-show');
			resizeBroadcast();
		});

		$('.sidebar-close').on('click', function(e){
			$('body').toggleClass('sidebar-opened').parent().toggleClass('sidebar-opened');
		});

		// Disable moving to top
		$('a[href="#"][data-top!=true]').on('click', function(e){
			e.preventDefault();
		});

		// User clicks on menu button
		$.hook('menulink').on('click', function(e) {
			var btn = this;
			Cliqm.menuLink(e, btn);
		});

		// User clicks on menu button
		$.hook('footerlink').on('click', function(e) {
			var btn = this;
			Cliqm.footerLink(e, btn);
		});
	    
	    $.hook('otherlink').on('click', function(e) {
	        var btn = this;
	        Cliqm.otherLink(e, btn);
	    });

    	$('.topbutton').on('click', function(e) {
    		e.preventDefault(); e.stopImmediatePropagation();
    		Cliq.topButton(this);
    	});	     	

	/* ---------- Introduce Page Scripts here  -------------*/

		@raw($scripts)

	/* ---------- Misc Scripts ---------------------------- */

		$('div.staticmap').each(function() {
			
			var mapid = $(this).attr('id');
			var data = $(this).data(), options = {
				zoom: data.zoom || 10,
				size: [data.width, data.height],
				lat: data.mapx,
				lng: data.mapy,
				markers: [
				   {lat: data.mapx, lng: data.mapy}
				]
			}; var map = $('#'+mapid).attr('src', GMaps.staticMapURL($options));		
		});	

}, function (error) {
    // There was an error fetching the script
    console.log(error);
}); 
</script>

</body>
</html>
