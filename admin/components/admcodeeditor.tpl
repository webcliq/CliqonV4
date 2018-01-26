<!-- Admin Code Editor Layout -->

<!-- Breadcrumb -->
<ol class="breadcrumb">
	<li class="breadcrumb-item capitalize">@(Q::cStr('70:Utilities'))</li>
	<li class="breadcrumb-item capitalize active">@(Q::cStr('224:Code Editor'))</li>
</ol>

<div class="container-fluid">
	<div class="animated fadeIn">
		<div class="row">
			<div class="col-lg-12">
				<div class="card left col-lg-12">	
					<div class="card-block">				
						@($title)
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
