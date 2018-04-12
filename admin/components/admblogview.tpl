<!--  AdmBlogView.Tpl simulates content side -->

<main class="container" id="dataview">
	<!-- Nav tabs -->
	<ul class="nav nav-tabs" role="tablist">
		<li class="nav-item">
			<a class="nav-link active" data-toggle="tab" href="#common" role="tab">@(Q::cStr('6:Common'))</a>
		</li>

		@foreach($idioms as $lcdcode => $lcdname)
		<li class="nav-item">
			<a class="nav-link" data-toggle="tab" href="#panel_@($lcdcode)" role="tab">@($lcdname)</a>
		</li>
		@endforeach
	</ul>

	<!-- Tab panes -->
	<div class="tab-content">
		<div class="tab-pane active" id="common" role="tabpanel">
			<div class="clqtable" id="clqtable">
				<div class="clqtable-row">
					<div class="clqtable-label">@(Q::cStr('5:Reference'))</div>
					<div class="clqtable-cell">@($row.c_reference)</div>
				</div>
				<div class="clqtable-row">
					<div class="clqtable-label">@(Q::cStr('420:Author'))</div>
					<div class="clqtable-cell">@($row.d_author)</div>
				</div>
				<div class="clqtable-row">
					<div class="clqtable-label">@(Q::cStr('183:Date'))</div>
					<div class="clqtable-cell">@($row.d_date)</div>
				</div>
				<div class="clqtable-row">
					<div class="clqtable-label">@(Q::cStr('199:Status'))</div>
					<div class="clqtable-cell">@($row.c_status)</div>
				</div>
				<div class="clqtable-row">
					<div class="clqtable-label">@(Q::cStr('196:Category'))</div>
					<div class="clqtable-cell">@($row.c_category)</div>
				</div>
				<div class="clqtable-row">
					<div class="clqtable-label">@(Q::cStr('574:Tags'))</div>
					<div class="clqtable-cell">@($row.c_options)</div>
				</div>
				<div class="clqtable-row">
					<div class="clqtable-label">@(Q::cStr('217:Image'))</div>
					<div class="clqtable-cell"><img src="@($row.d_image)" class="h120" /></div>
				</div>
				<div class="clqtable-row">
					<div class="clqtable-label">@(Q::cStr('8:Notes'))</div>
					<div class="clqtable-cell">@($row.c_notes)</div>
				</div>
			</div>
		</div>
		@foreach($idioms as $lcdcode => $lcdname)
		<div class="tab-pane" id="panel_@($lcdcode)" role="tabpanel">
			<div class="clqtable" id="clqtable">
				<div class="clqtable-row">
					<div class="clqtable-label">@(Q::cStr('130:Title'))</div>
					<div class="clqtable-cell">@($row['d_title'][$lcdcode])</div>
				</div>
				<div class="clqtable-row">
					<div class="clqtable-label">@(Q::cStr('456:Summary'))</div>
					<div class="clqtable-cell">@($row['d_description'][$lcdcode])</div>
				</div>
				<div class="clqtable-row">
					<div class="clqtable-label">@(Q::cStr('7:Content'))</div>
					<div class="clqtable-cell">@raw($row['d_text'][$lcdcode])</div>
				</div>
			</div>
		</div>
		@endforeach
	</div>

</main>