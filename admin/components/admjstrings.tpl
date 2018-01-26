<!-- Admin Javascript Language strings - Display and edit -->

<!-- Breadcrumb -->
<ol class="breadcrumb">

    <li class="right">@raw($topbuttons)</li>

	<li class="breadcrumb-item capitalize">@(Q::cStr('70:Utilities'))</li>
	<li class="breadcrumb-item capitalize active">@($title)</li>
</ol>

<div class="container-fluid">
	<div class="animated fadeIn">
        <div class="row">
            <div class="card">
                <div class="card-block">
                    <div class="col-12">
                        <table class="table table-sm table-striped table-bordered table-hover" id="datagrid"></table>
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

