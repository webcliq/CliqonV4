<!-- Admin Convert Array Layout -->

<!-- Breadcrumb -->
<ol class="breadcrumb">
	<li class="breadcrumb-item capitalize">@(Q::cStr('70:Utilities'))</li>
	<li class="breadcrumb-item capitalize active">@($title)</li>
</ol>

<div class="container-fluid">
	
	<div class="animated fadeIn">
		<div class="row" id="admconvertarray">
			
			<!-- Horizontal Form -->
			<div class="col-5">
				
				<div class="card card-outline-primary">	
					<div class="card-block">				
						<h4 class="card-title">@(Q::cStr('83:Convert Arrays'))</h4>
						<h6 class="card-subtitle mb-2 text-muted">@(Q::cStr('225:Use this form to transfer a CFG file to the database'))</h6>
						
						<form class="ml10 form form-horizontal" v-bind="inputform" id="inputform" method="post" enctype="multipart/form-data">
							
							<div class="form-group">
								<label for="tables">@(Q::cStr('126:Table'))</label>
								<select class="form-control col-12" id="tables" v-model="inputform.table" data-name="table">
									<option v-for="(ttbl, tval) in formdata.tables" v-bind:value="tval">[{{tval}}]&nbsp;{{ttbl}}</option>
								</select>
							</div>

							<div class="form-group">
								<label for="tabletypes">@(Q::cStr('226:Table Type'))</label>
								<select class="form-control col-12" id="tabletypes" v-model="inputform.tabletype" data-name="tabletype">
									<option v-for="(tbltype, ttval) in formdata.tabletypes" v-bind:value="ttval">[{{ttval}}]&nbsp;{{tbltype}}</option>
								</select>
								<small id="fileHelp" class="form-text text-muted">@(Q::cStr('165:Select a Table Type that is appropriate for the Table'))</small>
							</div>

							<div class="form-group ml5 dropzone">
								
								<!-- To be sorted when we sort out Fakepath 
								<label class="custom-file" style="width:100%">
									<input type="file" id="file" class="custom-file-input" placeholder="@(Q::cStr('9999:File input'))" name="inputfile" v-model="inputform.inputfile" style="width:100%" >
									<span class="custom-file-control" style="width:100%">{{inputform.inputfile}}</span>
								</label>


								-->

								<label for="inputfile">@(Q::cStr('186:File input'))</label>

								<input 
									type="file" 
									readonly 
									class="form-control-file col-12 border1 hlf-pad" 
									id="inputfile" 
									data-name="inputfile" 
									name="inputfile" 
									placeholder="@(Q::cStr('186:File input'))" 
								>

							</div>

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
				
				<div class="card card-outline-primary">
					<div class="card-block">				
						<h4 class="card-title">@(Q::cStr('228:Test Array'))</h4>
						<h6 class="card-subtitle mb-2 text-muted">@(Q::cStr('229:Upload a CFG array file and test it'))</h6>
						
						<form class="ml10 form form-horizontal" v-bind="testform" id="testform" method="post" enctype="multipart/form-data">
							
							<div class="form-group ml5 dropzone">
								
								<!-- To be sorted when we sort out Fakepath 
								<label class="custom-file" style="width:100%">
									<input type="file" id="file" class="custom-file-input" placeholder="@(Q::cStr('9999:File input'))" name="inputfile" v-model="inputform.inputfile" style="width:100%" >
									<span class="custom-file-control" style="width:100%">{{inputform.inputfile}}</span>
								</label>
								-->
								
								<label for="inputfile">@(Q::cStr('186:File input'))</label>
								<input type="file" class="form-control-file col-12 border1 hlf-pad" id="testfile" data-name="testfile" name="testfile" placeholder="@(Q::cStr('186:File input'))">

							</div>				

							<div class="form-group ml5">
								<button type="submit" class="btn btn-primary">@(Q::cStr('230:Test'))</button>
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
@raw($xtrascripts)
//]]>
</script>

