<!-- Admin Genkeys Layout -->

<!-- Breadcrumb -->
<ol class="breadcrumb">
	<li class="right">@raw($topbuttons)</li>
	<li class="breadcrumb-item capitalize">@(Q::cStr('70:Utilities'))</li>
	<li class="breadcrumb-item capitalize active">@(Q::cStr('246:Generate keys and manage tokens'))</li>
</ol>

<div class="container-fluid">
	
	<div class="animated fadeIn">
		<div class="row">
			<div class="col-lg-12">
				<div class="card left col-lg-12">	
					<div class="card-block">				
						@($content)
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
