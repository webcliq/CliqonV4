<!-- Admin Site Map Layout -->

<style>
	.CodeMirror {
		margin-top: 0px;
		padding: 5px;
		height: 600px;
		border: 1px solid lightgrey;
	}
</style>
<!-- Breadcrumb -->
<ol class="breadcrumb">

	<li class="right">@raw($topbuttons)</li>

	<li class="breadcrumb-item capitalize">@(Q::cStr('70:Utilities'))</li>
	<li class="breadcrumb-item capitalize">@(Q::cStr('77:Site Map'))</li>

</ol>

<div class="container-fluid">
	<div class="animated fadeIn">
		<div class="row" id="">
			
			<!-- Map  -->
			<div class="col-9">
				<div class="card">
					<div class="card-block minh36">
					@raw($mapdisplay)	
					</div>
				</div>
			</div>	

			<!-- Tree -->
			<div class="col-3">			
				<div class="card">
					<div class="card-block minh36">
					@raw($maptree)
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
