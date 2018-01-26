<!-- Admin Import Data Form Layout -->

<!-- Breadcrumb -->
<ol class="breadcrumb">

	<li class="right">@raw($topbuttons)</li>

	<li class="breadcrumb-item capitalize">@(Q::cStr('9999:Utilities'))</li>
	<li class="breadcrumb-item capitalize active">@($title)</li>
</ol>

<div class="container-fluid">
	
	<div class="animated fadeIn">
		<div class="row" id="admtestrig">
			
			<!-- Horizontal Form -->
			<div class="col-5">
				
				<div class="card card-outline-primary">	
					<div class="card-block">				
						<h4 class="card-title">@(Q::cStr('9999:Test Rig'))</h4>
						<h6 class="card-subtitle mb-2 text-muted">@(Q::cStr('9999:Test simultaneous file input and data upload'))</h6>
						
						<form class="ml10 form form-horizontal" v-bind="inputform" id="inputform" method="post" enctype="multipart/form-data">

							<!-- Select file to import  -->
							<div class="form-group ml5">
								<label for="inputfile">@(Q::cStr('9999:File input'))</label>
								<input type="file" class="form-control-file col-12 border1 hlf-pad" id="inputfile" data-name="inputfile" v-model="inputform.inputfile" placeholder="@(Q::cStr('9999:File input'))">
							</div>

							<!-- Any Data  -->
							<div class="form-group ml5">
								<label for="inputfile">@(Q::cStr('9999:Data'))</label>
								<input type="text" class="form-control col" data-name="anydata" v-model="inputform.anydata">
							</div>		

							<div class="form-group ml5">
								<button type="submit" class="btn btn-primary">@(Q::cStr('9999:Submit'))</button>
							</div>		

							<div class="form-group ml5">
								<span id="formresult">{{$data.inputform}}</span>
							</div>								

						</form>	
										
					</div>				
				</div>
			</div>

			<!-- Results block  -->
			<div class="col-7">
				<div class="card card-outline-secondary">	
					<div class="card-block">				
						<h4 class="card-title">@(Q::cStr('9999:Results'))</h4>
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


