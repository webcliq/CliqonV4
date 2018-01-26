<!-- Admin Card Layout to maintain Models -->

<!-- Breadcrumb -->
<ol class="breadcrumb">

	<li class="right">@raw($topbuttons)</li>

	<li class="breadcrumb-item capitalize active">@(Q::cStr('482:Models'))</li>

</ol>

<div class="container-fluid">
	<div class="animated fadeIn" id="datacard">
		
		<div class="row">

			<div class="col-3 left">
				<div class="card">	
					<div class="card-block">				
						<div class="card-text">
							<h6 class="redc capitalize fit c40">@(Q::cStr('234:Add new Record'))</h6> 
							<button type="button" class="btn btn-lg btn-danger" v-on:click="topButton($event, 'addrecord')">{{admfooter}}</button>
						</div>
					</div>
				</div>
			</div>

			<!-- Card Repeat -->
			<div class="col-3 left" v-for="(model, key) in admmodels">
				<div class="card">	
					<div class="card-block">				
						<div class="card-text"> 
							<h6 class="redc capitalize fit c40" v-bind:id="'id_'+key">{{model.c_reference}}</h6>
							<h6 class="bluec capitalize"></h6>
						</div>
					</div>	
				</div>
			</div>
			<!-- End Card Repeat -->
		</div>
	</div>
</main>

<script>
//<![CDATA[
@raw($xtrascripts)
//]]>
</script>

