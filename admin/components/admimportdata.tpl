<!-- Admin Import Data Form Layout -->

<!-- Breadcrumb -->
<ol class="breadcrumb">
	<li class="breadcrumb-item capitalize">@(Q::cStr('70:Utilities'))</li>
	<li class="breadcrumb-item capitalize active">@(Q::cStr('75:Import Data'))</li>
</ol>

<div class="container-fluid">
	
	<div class="animated fadeIn">
		<div class="row" id="admimportdata">
			
			<!-- Horizontal Form -->
			<div class="col-5">
				
				<div class="card card-outline-primary">	
					<div class="card-block">				
						<h4 class="card-title">@(Q::cStr('75:Import CSV'))</h4>
						<h6 class="card-subtitle mb-2 text-muted">@(Q::cStr('247:Use this form to upload and import a CSV file into the database'))</h6>
						
						<form class="ml10 form form-horizontal" v-bind="inputform" id="inputform" method="post" enctype="multipart/form-data">
							
							<!-- Table -->
							<div class="form-group">
								<label for="tables">@(Q::cStr('126:Table'))</label>
								<select class="form-control col-12" id="tables" v-model="inputform.table" data-name="table">
									<option v-for="(ttbl, tval) in formdata.tables" v-bind:value="tval">[{{tval}}]&nbsp;{{ttbl}}</option>
								</select>
							</div>

							<!-- Tabletype  -->
							<div class="form-group">
								<label for="tabletypes">@(Q::cStr('226:Table Type'))</label>
								<select class="form-control col-12" id="tabletypes" v-model="inputform.tabletype" data-name="tabletype">
									<option v-for="(tbltype, ttval) in formdata.tabletypes" v-bind:value="ttval">[{{ttval}}]&nbsp;{{tbltype}}</option>
								</select>
								<small id="fileHelp" class="form-text text-muted">@(Q::cStr('243:Select a Table Type that is appropriate for the Table'))</small>
							</div>

							<!-- Select file to import  -->
							<div class="form-group ml5">
								<label for="inputfile">@(Q::cStr('186:File input'))</label>
								<input type="file" class="form-control-file col-12 border1 hlf-pad" id="inputfile" data-name="inputfile" v-model="inputform.inputfile" placeholder="@(Q::cStr('186:File input'))">
							</div>

							<!-- Delimiter = ','  -->
							<div class="form-group ml5">
								<label for="inputfile">@(Q::cStr('248:Row delimiter or termination character'))</label>
								<input type="text" class="form-control col-2" data-name="delimiter" v-model="inputform.delimiter">
							</div>

							<!-- Encloser = '"'  -->
							<div class="form-group ml5">
								<label for="inputfile">@(Q::cStr('249:Field encloser character'))</label>
								<input type="text" class="form-control col-2" data-name="encloser" v-model="inputform.encloser">
							</div>

							<!-- Escape = '\'  -->
							<div class="form-group ml5">
								<label for="inputfile">@(Q::cStr('250:Escape character'))</label>
								<input type="text" class="form-control col-2" data-name="escape" v-model="inputform.escape">
							</div>

							<!-- Longestline = 0   -->
							<div class="form-group ml5">
								<label for="inputfile">@(Q::cStr('251:Longest line (0) no limit'))</label>
								<input type="text" class="form-control col-2" data-name="longestline" v-model="inputform.longestline">
							</div>

							<!-- Header = check -->
							<div class="form-check ml5">
								<label class="custom-control custom-checkbox">
								  <input type="checkbox" class="custom-control-input" v-model="inputform.header" data-name="header">
								  <span class="custom-control-indicator"></span>
								  <span class="custom-control-description">@(Q::cStr('252:Include Header'))</span>
								</label>
							</div>	

							<!-- Write to Database  -->
							<div class="form-check ml5">
								<label class="custom-control custom-checkbox">
								  <input type="checkbox" class="custom-control-input" v-model="inputform.dbwrite" data-name="dbwrite">
								  <span class="custom-control-indicator"></span>
								  <span class="custom-control-description">@(Q::cStr('227:Write to Database'))</span>
								</label>
							</div>				
							
							<div class="form-group ml5">
								<button type="submit" class="btn btn-primary">@(Q::cStr('105:Submit'))</button>
							</div>	

							<!-- Test Only   -->
							<span id="formresult" style="display:none;">{{$data.inputform}}</span>

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


