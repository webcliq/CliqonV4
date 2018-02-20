<!-- Admin Gallery Layout -->
<link href="@($viewpath)css/galleria.classic.css" rel="stylesheet">
<!-- Breadcrumb -->
<ol class="breadcrumb">

	<li class="right">@raw($topbuttons)</li>

	<li class="breadcrumb-item capitalize">@(Q::cStr('57:Gallery'))</li>
	<li class="breadcrumb-item capitalize">@($table)</li>
	<li class="breadcrumb-item capitalize active">@($tabletype)</li>
</ol>

<div class="container-fluid">
	
	<div class="animated fadeIn">
		<div class="row">
			<div class="col-12">				
				@raw($gallery)
			</div>		
		</div>
	</div>
</div>

<script>
//<![CDATA[
@raw($xtrascripts)
//]]>
</script>
