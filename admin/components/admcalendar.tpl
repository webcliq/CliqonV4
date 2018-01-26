<!-- Admin Calendar Layout -->

<!-- Breadcrumb -->
<ol class="breadcrumb">

	<li class="right">@raw($topbuttons)</li>

	<li class="breadcrumb-item capitalize">@(Q::cStr('56:Calendar'))</li>
	<li class="breadcrumb-item capitalize">@($table)</li>
	<li class="breadcrumb-item capitalize active">@($tabletype)</li>
</ol>

<div class="container-fluid">
	
	<div class="animated fadeIn">
		<div class="row">
			<div class="col-lg-12">
				<div class="card left col-lg-12">	
					<div class="card-block h740">	
					@raw($calendar)
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
