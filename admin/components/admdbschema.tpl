<!-- Admin Database Schema Layout -->
<!-- Breadcrumb -->
<ol class="breadcrumb">
	<li class="right">@raw($topbuttons)</li>
	<li class="breadcrumb-item capitalize">@(Q::cStr('70:Utilities'))</li>
	<li class="breadcrumb-item capitalize active">@(Q::cStr('241:Database Dictionary and Schema'))</li>
</ol>

<div class="container-fluid">
	<div class="animated fadeIn">
		@raw($content)
	</div>
</div>

<script>
//<![CDATA[
@raw($xtrascripts)
//]]>
</script>
