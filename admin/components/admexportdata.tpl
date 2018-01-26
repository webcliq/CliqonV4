<!-- Admin Export data Layout -->

<!-- Breadcrumb -->
<ol class="breadcrumb">
	<li class="breadcrumb-item capitalize">@(Q::cStr('70:Utilities'))</li>
	<li class="breadcrumb-item capitalize active">@(Q::cStr('73:Export Data'))</li>
</ol>

<div class="container-fluid">
	
	<div class="animated fadeIn">
		<div class="row" id="admexportdata">
			
			<!-- Horizontal Form -->
			<div class="col-5">
				
				<div class="card card-outline-primary">	
					<div class="card-block">				
						<h4 class="card-title">@(Q::cStr('73:Export Data'))</h4>
						<h6 class="card-subtitle mb-2 text-muted">@(Q::cStr('242:Use this form to select data for export to a CSV or Array Config file'))</h6>
						
						<form class="ml10 form form-horizontal" v-bind="exportform" id="exportform" method="post" enctype="multipart/form-data">
							
							<div class="form-group">
								<label for="tables">@(Q::cStr('126:Table'))</label>
								<select class="form-control col-12" id="tables" v-model="exportform.table" data-name="table">
									<option v-for="(ttbl, tval) in formdata.tables" v-bind:value="tval">[{{tval}}]&nbsp;{{ttbl}}</option>
								</select>
							</div>

							<div class="form-group">
								<label for="tabletypes">@(Q::cStr('226:Table Type'))</label>
								<select class="form-control col-12" id="tabletypes" v-model="exportform.tabletype" data-name="tabletype">
									<option v-for="(tbltype, ttval) in formdata.tabletypes" v-bind:value="ttval">[{{ttval}}]&nbsp;{{tbltype}}</option>
								</select>
								<small id="fileHelp" class="form-text text-muted">@(Q::cStr('243:Select a Table Type that is appropriate for the Table'))</small>
							</div>

							<div class="form-check ml5">
								<label class="custom-control custom-checkbox">
								  <input type="checkbox" class="custom-control-input" v-model="exportform.csvorarray" data-name="csvorarray">
								  <span class="custom-control-indicator"></span>
								  <span class="custom-control-description">@(Q::cStr('244:Check box for output to CSV file'))</span>
								</label>
							</div>	

							<div class="form-check ml5">
								<label class="custom-control custom-checkbox">
								  <input type="checkbox" class="custom-control-input" v-model="exportform.doexport" data-name="doexport">
								  <span class="custom-control-indicator"></span>
								  <span class="custom-control-description">@(Q::cStr('245:Check to export file'))</span>
								</label>
							</div>								

							<div class="form-group ml5">
								<button type="submit" class="btn btn-primary">@(Q::cStr('105:Submit'))</button>
							</div>	

						</form>	
										
					</div>								
				</div>
			</div>	
			
			<!-- Results block  -->
			<div class="col-7">
				<div class="card card-outline-secondary">	
					<div class="card-block">				
						<h4 class="card-title">@(Q::cStr('231:Results'))</h4>
						<p class="card-text" id="convertresults"></p>
					</div>				
				</div>
			</div>	
							
		</div>
	</div>
</div>							

<script>
//<![CDATA[
@raw($dtscripts)
//]]>
</script>
