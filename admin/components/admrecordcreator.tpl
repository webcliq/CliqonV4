<!-- Admin Record Managemenmt with Grid and Form Column Layout -->
<style>
#dataform .CodeMirror{border: 1px solid #C2CFD6; height: 660px;}
</style>
<!-- Breadcrumb -->
<ol class="breadcrumb">

	<li class="right">@raw($topbuttons)</li>

	<li class="breadcrumb-item capitalize">@(Q::cStr('9999:Record Manager'))</li>

</ol>

<div class="container-fluid">
	<div class="animated fadeIn">
		<div class="row" id="">
			
			<!-- Grid -->
			<div class="col-6">			
				<div class="card">
					<div class="card-block">
					@raw($admdatagrid)
					</div>	
				</div>
			</div>

			<!-- Results  -->
			<div class="col-6">
				<div class="card">
					<div class="card-block minh34">
					@raw($admresults)
					</div>
				</div>
			</div>	
							
		</div>
	</div>
</div>
