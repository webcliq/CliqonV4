<!-- Admin Report Layout -->

<!-- Breadcrumb -->
<ol class="breadcrumb">
	<li class="breadcrumb-item capitalize">@(Q::cStr('61:Report'))</li>
	<li class="breadcrumb-item capitalize">@($title)</li>
</ol>

<div class="container-fluid">
	
	<div class="animated fadeIn">
		<div class="row">
			<div class="col-lg-12">
				<div class="card">	
					<div class="card-block" >				
						@raw($dtable)
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
