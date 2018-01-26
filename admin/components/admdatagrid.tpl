<!-- Admin Grid Component Layout -->

<!-- Breadcrumb -->
<ol class="breadcrumb">

	<li class="right">@raw($topbuttons)</li>

	<li class="breadcrumb-item capitalize">@(Q::cStr('236:Grid'))</li>
	<li class="breadcrumb-item capitalize">@($table)</li>
	<li class="breadcrumb-item capitalize active">@($tabletype)</li>

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

<script>
//<![CDATA[
@raw($xtrascripts)
//]]>
</script>


