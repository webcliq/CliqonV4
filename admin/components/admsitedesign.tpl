<!-- Admin Genkeys Layout -->

<!-- Breadcrumb -->
<ol class="breadcrumb">
	<li class="right">@raw($topbuttons)</li>
	<li class="breadcrumb-item capitalize">@(Q::cStr('503:Site Designer'))</li>
	<li class="breadcrumb-item capitalize">@($table)</li>
	<li class="breadcrumb-item capitalize active">@($tabletype)</li>
</ol>

<div id="sitedesigner" class="mt-20">
	@($content)	
</div>

<script>
//<![CDATA[
@raw($xtrascripts)
//]]>
</script>
