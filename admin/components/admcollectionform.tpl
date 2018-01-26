<!-- Admin Collection Form Layout -->

<!-- Breadcrumb -->
<ol class="breadcrumb">
	<li class="breadcrumb-item capitalize">@($table)</li>
	<li class="breadcrumb-item capitalize">@($tabletype)</li>
	<li class="breadcrumb-item capitalize active">@(Q::cStr('9999:Maintain'))</li>
</ol>

<div class="container-fluid">
	
	<div class="animated fadeIn">
		<div class="row">
			
			<div class="col-lg-12">
				<div class="card left col-lg-12">	
					<div class="card-block">				
						@raw($formhtml)
					</div>				
				</div>
			</div>	
			
			<div class="col-lg-12">
				<div class="card left col-lg-12">	
					<div class="card-block">				
						@raw($fieldhtml)
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
