<!-- Admin Manage Languages Layout -->

<!-- Breadcrumb -->
<ol class="breadcrumb">
	<li class="breadcrumb-item capitalize">@(Q::cStr('70:Utilities'))</li>
	<li class="breadcrumb-item capitalize active">@($title)</li>
</ol>

<div class="container-fluid">
	
	<div class="animated fadeIn">
		<div class="row" id="admmaintainidiom">
			
			<!-- Horizontal Form -->
			<div class="col">
				<div class="card card-outline-primary">	
					<div class="card-block">				
						<h4 class="card-title">@(Q::cStr('253:System Languages'))</h4>
						<h6 class="card-subtitle mb-2 text-muted">@(Q::cStr('254:Use this grid to add or delete system languages'))</h6>
						
						<a v-for="(idm, idx) in idioms" href="#" class="btn btn-danger mr5"><i class="fa fa-trash mr5" v-on:click="deleteIdiom(idx)"></i>{{idm}}</a>
						
						<a href="#" class="btn btn-primary right" v-on:click="addIdiom"><i class="fa fa-plus mr5"></i>@(Q::cStr('255:Add Language'))</a>
					</div>				
				</div>
				
				<div class="card card-outline-success hide" id="addidiomform">	
					<div class="card-block">				
						<h4 class="card-title">@(Q::cStr('255:Add Language'))</h4>
						<h6 class="card-subtitle mb-2 text-muted">@(Q::cStr('256:Identify and upload the Language file. Complete other fields. Press Test or Submit'))</h6>
						
						<form class="ml10 form form-horizontal" v-bind="inputform" id="inputform" method="post" enctype="multipart/form-data">

							<div class="form-group">
								<label for="lcdcode">@(Q::cStr('258:Language Code'))</label>
								<input type="text" id="lcdcode" v-model="inputform.lcdcode" data-name="lcdcode" class="form-control col-2" autofocus required min="2" pattern="[a-z]{2}" placeholder="zz">
							</div>

							<div class="form-group">
								<label for="lcdname">@(Q::cStr('259:Language Name'))</label>
								<input type="text" id="lcdname" v-model="inputform.lcdname" data-name="lcdname" class="form-control col-8" required max="20"  placeholder="Zulu">
								<small id="fileHelp" class="form-text text-muted">@(Q::cStr('260:It is suggested that the language name be in the idiom of the language - eg. es = Español'))</small>
							</div>

							<div class="form-group ml5">
					
								<label for="inputfile">@(Q::cStr('186:File input'))</label>
								<input type="file" readonly class="form-control form-control-file" data-name="inputfile" placeholder="@(Q::cStr('186:File input'))" id="filefield">

							</div>

							<div class="form-check ml5">

								<label class="custom-control custom-checkbox">
								  	<input type="checkbox" class="custom-control-input" v-model="inputform.dbwrite" data-name="dbwrite">
								  	<span class="custom-control-indicator"></span>
								  	<span class="custom-control-description">@(Q::cStr('227:Write to Database'))</span>
								</label>

								<label class="custom-control custom-checkbox">
								  	<input type="checkbox" class="custom-control-input" v-model="inputform.cfgwrite" data-name="cfgwrite">
								  	<span class="custom-control-indicator"></span>
								  	<span class="custom-control-description">@(Q::cStr('261:Write Config file'))</span>
								</label>

							</div>

							<button type="submit" class="btn btn-primary">@(Q::cStr('105:Submit'))</button>
							
							<!-- Test Only -->
							<span id="formresult" style="display:none;">{{$data.inputform}}</span>

						</form>	
										
					</div>				
				</div>
				
				<div class="card card-outline-success">	
					<div class="card-block">				
						<h4 class="card-title">@(Q::cStr('262:Create template'))</h4>
						<h6 class="card-subtitle mb-2 text-muted">@(Q::cStr('263:Download a template'))</h6>
						
						<a href="#" class="btn btn-primary" v-on:click="downloadTemplate"><i class="fa fa-cog mr5"></i>@(Q::cStr('264:Download'))</a>
					</div>				
				</div>
			</div>	
			
			<!-- Results block  -->
			<div class="col">
				<div class="card card-outline-secondary">	
					<div class="card-block">				
						<h4 class="card-title">@(Q::cStr('231:Results'))</h4>
						<p class="card-text" id="idiomresults"></p>
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
