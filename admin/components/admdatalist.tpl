<!-- Admin List Layout creates a admin layout template -->

<!-- Breadcrumb -->
<ol class="breadcrumb">

	<li class="right">@raw($topbuttons)</li>

	<li class="breadcrumb-item capitalize active">@(Q::cStr('237:Datalist'))</li>
	<li class="breadcrumb-item capitalize">@($table)</li>
	<li class="breadcrumb-item capitalize active">@($tabletype)</li>	

</ol>

<div class="container-fluid">
	<div class="animated fadeIn">
		<div class="row" id="">
			
			<!-- Grid -->
			<div class="col-6">			
				<div class="card">
					<div class="card-block" id="@($tblopts.tableId)">

						<!-- Search -->
						<div class="card-header bg-secondary inverse">
							<form class="form-inline">
								<div class="form-group right">
									<input class="form-control smaller" name="searchfield" id="searchfield" placeholder="@(Q::cStr('149:Search'))" />
								</div>
								<button type="button" style="margin: 0 0 0 10px;" v-on:click="searchbutton($event)" class="btn btn-sm btn-warning">@(Q::cStr('239:Search for'))</button>
								<button type="button" style="margin: 0 0 0 10px;" v-on:click="clearbutton($event)" class="btn btn-sm btn-info">@(Q::cStr('122:Reset'))</button>
							</form>
						</div>					

						<!-- Data List -->
						<div class="list-group mt10" style="@($tblopts.style)">
							<div v-for="(item, key) in rows" class="list-group-item list-group-item-action flex-column align-items-start" v-bind:id="'id_'+key">
								<!-- Image here -->
								<div class="d-flex w-100">
									<h6 class="">
										<i v-for="(icon, ikey) in listicons" v-on:click="listButton($event, item, key, ikey)" v-bind:class="'pointer bluec fa fa-fw fa-'+icon.icon"></i>
										<span class="text-muted rp5">{{item.id}}</span>
										<span class="redc bold">{{item.c_reference}}</span>
									</h6>
								</div>
								<p class="mb-1">{{item.c_notes}}</p>
							</div>
						</div>

						<!-- Pagination -->
						<div class="card-text mt10">

	                    	<span class="left mt5" style="vertical-align: bottom;" id="paginationtext">
	                    		<span class="ucfirst">{{records.recordstxt}}</span>
	                    		&nbsp;{{records.start}}
	                    		&nbsp;{{records.fromtxt}}
	                    		&nbsp;{{records.end}}
	                    		&nbsp;{{records.totxt}}
	                    		&nbsp;{{records.total}}
	                    	</span>

	                    	<span>
	                    		<select class="form-control form-control-sm left ml10" name="pageselect" id="pageselect" style="width: 60px;">
	                    			<option disabled value="">@(Q::cStr('108:Select'))</option>
	                    			<option class="" v-for="opt in pagerselect" v-bind:value="opt.value" :selected="selected == opt.value">{{opt.text}}</option>
	                    		</select>
	                    	</span>

		                    <nav class="right">
		                        <ul class="pagination smaller" id="tablepagination"></ul>
		                    </nav>

		                </div>

	                </div>
				</div>
			</div>

			<!-- Results  -->
			<div class="col-6">
				<div class="card">
					<div class="card-block minh34">
					<div id="columnform"></div>
					</div>
				</div>
			</div>	
							
		</div>
	</div>
</div>

<script>
//<![CDATA[
@raw($xtrascripts)
//]]>
</script>

