<!-- Admin Card Layout creates a admin layout template -->

<!-- Breadcrumb -->
<ol class="breadcrumb">

	<li class="right">@raw($topbuttons)</li>

	<li class="breadcrumb-item capitalize active">@(Q::cStr('233:Datacard'))</li>
	<li class="breadcrumb-item capitalize">@($table)</li>
	<li class="breadcrumb-item capitalize active">@($tabletype)</li>	

</ol>

<div class="container-fluid">
	<div class="animated fadeIn" id="datacard">
		
		<div class="row">

			<div class="col-3 left" v-if="admfooter != ''">
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
			<div class="col-3 left" v-for="(card, key) in admdatacards">
				<div class="card">	
					<div class="card-block">				
						<div class="card-text"> 
							<h6 class="redc capitalize fit c40" v-bind:id="'id_'+key">{{card.c_reference}}</h6>
							<h6 class="bluec capitalize" v-html="card.c_common"></h6>
							<div class="btn-group">

								<!-- List -->
								<a href="#" role="button" class="btn btn-link btn-sm pad5 m0 hint--top" aria-label="@(Q::cStr('111:Display records'))" v-on:click="listButton($event, card, key)">@(Q::cStr('101:List'))</a>

								<!-- View -->
								<a href="#" role="button" class="btn btn-link btn-sm pad5 m0 orangec hint--top" aria-label="@(Q::cStr('112:View record'))" v-on:click="viewButton($event, card, key)">@(Q::cStr('102:View'))</a>

								<!-- Edit -->
								<a href="#" role="button" class="btn btn-link btn-sm pad5 m0 hint--top" aria-label="@(Q::cStr('113:Edit collection'))" v-on:click="editButton($event, card, key)">@(Q::cStr('103:Edit'))</a>

								<!-- Delete -->
								<a href="#" role="button" class="btn btn-link btn-sm pad5 m0 hint--top" aria-label="@(Q::cStr('235:Delete record'))" v-on:click="deleteButton($event, card, key)">@(Q::cStr('104:Delete'))</a>

							</div>
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

