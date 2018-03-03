<!-- Admin Report Designer -->

<!-- Breadcrumb -->
<ol class="breadcrumb">

	<li class="right">@raw($topbuttons)</li>

	<li class="breadcrumb-item capitalize">@(Q::cStr('61:Reports'))</li>
	<li class="breadcrumb-item capitalize active">@(Q::cStr('62:Report Designer'))</li>
</ol>

<div class="container-fluid">
	<div class="animated fadeIn">
		<div class="row" id="reportdesigner">

			<!-- Results  -->
			<div class="col-4">
				<div class="card">
					<div class="card-block pad10">
					@raw($designtabs)	
					</div>
				</div>
			</div>				
			
			<!-- Grid -->
			<div class="col-8">			
				<div class="card" style="min-height: 846px;">
					@raw($designgrid)
				</div>
			</div>
				
		</div>
	</div>
</div>
