<!-- Admin Site Update Layout -->

<!-- Breadcrumb -->
<ol class="breadcrumb">

	<li class="right">@raw($topbuttons)</li>

	<li class="breadcrumb-item capitalize">@(Q::cStr('70:Utilities'))</li>
	<li class="breadcrumb-item capitalize">@(Q::cStr('82:Site Update'))</li>

</ol>

<div class="container-fluid">
	<div class="animated fadeIn">
		<div class="row" id="">
			
			<!-- Grid -->
			<div class="col-6">			
				<div class="card">
					<div class="card-block minh36">
					@raw($filelist)
					</div>	
				</div>
			</div>

			<!-- Results  -->
			<div class="col-6">
				<div class="card">
					<div class="card-block minh36">
					@raw($rssfeed)	
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
