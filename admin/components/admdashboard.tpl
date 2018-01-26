<!-- Admin Dashboard -->
<style>
@@media (min-width: 34em) {
    .card-columns {
        -webkit-column-count: 1;
        -moz-column-count: 1;
        column-count: 1;
    }
}

@@media (min-width: 48em) {
    .card-columns {
        -webkit-column-count: 2;
        -moz-column-count: 2;
        column-count: 2;
    }
}

@@media (min-width: 62em) {
    .card-columns {
        -webkit-column-count: 3;
        -moz-column-count: 3;
        column-count: 3;
    }
}

@@media (min-width: 75em) {
    .card-columns {
        -webkit-column-count: 4;
        -moz-column-count: 4;
        column-count: 4;
    }
}
</style>
<!-- Breadcrumb -->
<ol class="breadcrumb">

	<li class="right">@raw($tablebuttons)</li>

	<li class="breadcrumb-item capitalize active">@(Q::cStr('11:Dashboard'))</li>
	<li class="breadcrumb-item text-muted small mt5">@(Q::cStr('232:Drag panels by selecting'))&nbsp;<i class="fa fa-fw fa-arrows"></i></li>

</ol>

<div class="container-fluid">
	<div class="animated fadeIn">
		<div class="row">
			<div class="col-12">
				<div class="card">	
					<div class="card-block">	

						<section class="row">
							<!--Multiple Panels-->
							<div class="card-columns col-12" id="clqpanel">
								<!-- Card repeat -->
								<!--
									id => ckey => key of the subarray
									settings are in crd.options, eg crd.options.footer or crd.options.text
								-->
							  	<div class="card" v-for="(crd, ckey) in admdbpanels" v-bind:id="ckey">
									<div class="card-block card-dashboard">

										<div class="card-text mt-10">
											<span class="text-muted right mr-10 rp0">						
												<i 
													v-for="(itm, ikey) in admdbicons" 
													v-if="ikey == 'settings' && crd.options.settings != false || ikey != 'settings'" 
													v-bind:class="'fa fa-fw fa-' + itm.icon" 
													v-bind:title="itm.tooltip"  
													v-bind:style="'cursor:'+itm.cursor" 
													v-on:click="iconAction($event, itm, crd.options, ckey)" 
												></i>
											</span>
											<h5 class="left" v-html="crd.title"></h5>									
										</div>

										<div class="card-text mt30 mr10" v-bind:id="ckey+'_content'">
											<div class="img-fluid" v-html="crd.options.text"></div>
											<!-- crd.footer has lost the correct formatting -->
											<div class="text-muted" v-html="crd.options.footer"></div>
										</div>

									</div>
							  	</div>	
							  	<!-- End repeat -->

							</div>					
						</section>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- /.conainer-fluid -->
<script>
//<![CDATA[
@raw($xtrascripts)
//]]>
</script>
